<?php

$job_strings[] = 'getCustomers';
$job_strings[] = 'getProducts';
$job_strings[] = 'getInvoices';

require_once 'custom/include/Kashflow/Kashflow.php';
require_once 'modules/AOS_Products/AOS_Products.php';
require_once 'modules/AOS_Products_Quotes/AOS_Products_Quotes.php';
require_once 'modules/AOS_Invoices/AOS_Invoices.php';
require_once 'modules/Accounts/Account.php';

/**
 * @return bool
 */
function getCustomers() {
    $kashflow = new Kashflow();
    $response = $kashflow->getCustomers();
    if ($response->Status !== "OK") {
        return false;
    }

    $customersArray = [];
    if (!empty($response->GetCustomersResult->Customer->CustomerID)) {
        $customersArray[] = $response->GetCustomersResult->Customer;
    } else {
        $customersArray = $response->GetCustomersResult->Customer;
    }

    $existingInDb = checkIfKashFlowRecordsExists($customersArray, 'accounts', ['CustomerID' => 'kashflow_id']);

    $newlyCreated = 0;
    $maxNewRecords = 50;
    foreach($customersArray as $customer) {
        if(empty($customer->CustomerID)) {
            continue;
        }
        $beanId = array_search($customer->CustomerID, $existingInDb);
        if ($beanId !== false) {
            updateAccount($beanId, $customer);
            continue;
        }
        createAccount($customer);
        $newlyCreated++;
        if ($newlyCreated > $maxNewRecords) {
            break;
        }
    }
    return true;
}

/**
 *
 */
function getProducts() {
    global $app_list_strings;

    $kashflow = new Kashflow();
    foreach($app_list_strings['kashflow_nominal_codes'] as $code => $label) {
        $response = $kashflow->getSubProducts($code);
        if ($response->Status == "OK") {
            $productsArray = array();
            if(!empty($response->GetSubProductsResult->SubProduct->id))
                $productsArray[] = $response->GetSubProductsResult->SubProduct;
            else
                $productsArray = $response->GetSubProductsResult->SubProduct;
            foreach($productsArray as $product) {
                if(!empty($product->id)) {
                    // Find based on Kashflow ID
                    $productBean = new AOS_Products();
                    $productBean->retrieve_by_string_fields(array('kashflow_id' => $product->id));
                    if(!empty($productBean->id)) {
                        if (checkIfChangedProduct($productBean, $product) == true) updateProduct($productBean, $product);
                    } else updateProduct($productBean, $product);
                }
            }
            // return true;
        }
    }
}

/**
 *
 */
function getInvoicesFromLast24Hours() {
    getInvoicesFromInterval('1 day', 10);
}

/**
 *
 */
function getInvoicesFromLastWeek() {
    getInvoicesFromInterval('1 week', 20);
}

/**
 *
 */
function getInvoicesFromLastMonth() {
    getInvoicesFromInterval('1 month', 50);
}

/**
 * @param string $interval
 * @param int $maxNewRecords
 */
function getInvoicesFromInterval($interval, $maxNewRecords = 50) {

    global $timedate;

    // Set script timeout to 10 hours
    ini_set("max_execution_time", "3600");

    $end = $timedate->getNow();
    $interval1 = DateInterval::createFromDateString($interval);
    $start = $timedate->getNow()->sub($interval1);

    $kashflow = new Kashflow();
    $response = $kashflow->getInvoicesByDateRange(
        str_replace(' ', 'T', $timedate->asDb($start)),
        str_replace(' ', 'T', $timedate->asDb($end))
    );
    saveInvoiceResponse($response, $maxNewRecords);
}

/**
 *
 */
function getAllInvoices() {

    global $timedate;

    // Set script timeout to 10 hour
    ini_set("max_execution_time", "36000");

    $interval = DateInterval::createFromDateString('1 month');
    $start = $timedate->fromDbDate('2000-01-01');
    $end = $timedate->getNow();

    $kashflow = new Kashflow();
    while ($start < $end) {
        $response = $kashflow->getInvoicesByDateRange(
            str_replace(' ', 'T', $timedate->asDb($start)),
            str_replace(' ', 'T', $timedate->asDb($start->add($interval)))
        );
        saveInvoiceResponse($response, 0);
    }
}

/**
 * @param $response
 * @param int $maxNewRecords
 * @return bool
 */
function saveInvoiceResponse($response, $maxNewRecords = 50) {
    if ($response->Status !== "OK") {
        return false;
    }

    $invoiceArray = [];
    if (!empty($response->GetInvoicesByDateRangeResult->Invoice->InvoiceDBID)) {
        $invoiceArray[] = $response->GetInvoicesByDateRangeResult->Invoice;
    } else {
        $invoiceArray = $response->GetInvoicesByDateRangeResult->Invoice;
    }

    $fieldsToCheck = [
        'InvoiceDBID' => 'kashflow_id',
        'InvoiceNumber' => 'number',
    ];
    $existingInDb = checkIfKashFlowRecordsExists($invoiceArray, 'aos_invoices', $fieldsToCheck);
    $newlyCreated = 0;
    foreach($invoiceArray as $invoice) {
        $beanId = array_search($invoice->InvoiceDBID, $existingInDb);
        if ($beanId !== false) {
            updateInvoice($invoice);
        }
        else {
            $beanId = createInvoice($invoice);
            $newlyCreated++;
        }
        updateLineItems($beanId, $invoice);
        if ($maxNewRecords && $newlyCreated > $maxNewRecords) {
            break;
        }
    }
    return true;
}

/**
 * @param array $recordArray
 * @param string $table
 * @param array $fieldsToCheck
 * @param string $subFieldName
 * @return array
 */
function checkIfKashFlowRecordsExists($recordArray, $table, $fieldsToCheck, $subFieldName = null)
{
    global $db;
    $existing = [];
    $arrayFieldsToCheck = [];
    foreach ($fieldsToCheck as $recordName => $dbName) {
        $arrayFieldsToCheck[$dbName] = [];
    }
    foreach ($recordArray as $record) {
        foreach ($fieldsToCheck as $recordName => $dbName) {
            if ($subFieldName) {
                $arrayFieldsToCheck[$dbName][] = $record->$subFieldName->$recordName;
                continue;
            }
            $arrayFieldsToCheck[$dbName][] = $record->$recordName;
        }
    }
    $sql = "SELECT id," . implode(',', $fieldsToCheck) . " FROM $table WHERE ";
    foreach ($arrayFieldsToCheck as $fieldName => $values) {
        $sql .= "OR $fieldName IN (" . implode(',', $values) . ") ";
    }
    $sql = str_replace('WHERE OR', 'WHERE', $sql);
    $result = $db->query($sql);
    $fieldToReturn = array_shift($fieldsToCheck);
    while ($row = $db->fetchByAssoc($result)) {
        $existing[$row['id']] = $row[$fieldToReturn];
    }

    return $existing;
}

/**
 * @param $parentId
 * @param $customer
 */
function updateAccount($parentId, $customer) {

    global $db;

    $sql = "UPDATE accounts " .
        "SET kashflow_code = '" . $customer->Code . "', " .
        "name = '" . $customer->Name . "', " .
        "phone_office = '" . $customer->Telephone . "', " .
        "phone_fax = '" . $customer->Fax . "', " .
        "billing_address_street = '" . $customer->Address1 . "', " .
        "billing_address_city = '" . $customer->Address2 . "', " .
        "billing_address_state = '" . $customer->Address3 . "', " .
        "billing_address_country = '" . $customer->Address4 . "', " .
        "billing_address_postalcode = '" . $customer->Postcode . "', " .
        "website = '" . $customer->Website . "' " .
        "WHERE id = '" . $parentId . "'";
    $db->query($sql);

    // Now handle Billing Contact
    if(!empty($customer->ContactFirstName) && !empty($customer->ContactLastName)) {
        $sql = "UPDATE contacts " .
            "SET salutation = '" . $customer->ContactTitle . "', " .
            "first_name = '" . $customer->ContactFirstName . "', " .
            "last_name = '" . $customer->ContactLastName . "', " .
            "phone_mobile = '" . $customer->Mobile . "' " .
            "WHERE billing_contact = 1 AND account_id = '" . $parentId . "'";
        $db->query($sql);
    }
}

/**
 * @param $customer
 */
function createAccount($customer) {

    $accountBean = new Account();
    $accountBean->kashflow_id = $customer->CustomerID;
    $accountBean->kashflow_code = $customer->Code;
    $accountBean->name = $customer->Name;
    $accountBean->phone_office = $customer->Telephone;
    $accountBean->phone_fax = $customer->Fax;
    $accountBean->email1 = $customer->Email;
    $accountBean->billing_address_street = $customer->Address1;
    $accountBean->billing_address_city = $customer->Address2;
    $accountBean->billing_address_state = $customer->Address3;
    $accountBean->billing_address_country = $customer->Address4;
    $accountBean->billing_address_postalcode = $customer->Postcode;
    $accountBean->website = $customer->Website;
    $accountBean->from_kashflow = true;
    $accountBean->save();

    // Now handle Billing Contact
    if(!empty($customer->ContactFirstName) && !empty($customer->ContactLastName)) {
        $accountBean->load_relationship("contacts");
        $contactBean = new Contact();
        $accountBean->contacts->getBeans();
        foreach ($accountBean->contacts->beans as $contact) {
            if ($contact->billing_contact == true) {
                $contactBean = $contact;
                break;
            }
        }

        $contactBean->salutation = $customer->ContactTitle;
        $contactBean->first_name = $customer->ContactFirstName;
        $contactBean->last_name = $customer->ContactLastName;
        $contactBean->phone_mobile = $customer->Mobile;
        $contactBean->billing_contact = true;
        $contactBean->account_id = $accountBean->id;
        $contactBean->from_kashflow = true;
        $contactBean->save();
    }
}

/**
 * @param $productBean
 * @param $product
 */
function updateProduct($productBean, $product) {

    $productBean->kashflow_id = $product->id;
    $productBean->nominal_code = $product->ParentID;
    $productBean->name = $product->Name;
    $productBean->part_number = $product->Code;
    $productBean->description = $product->Description;
    $productBean->price = $product->Price;
    $productBean->vat_rate = $product->VatRate;
    $productBean->cost = $product->WholesalePrice;
    $productBean->managed = $product->Managed;
    $productBean->qty_in_stock = $product->QtyInStock;
    $productBean->stock_warn_qty = $product->StockWarnQty;
    $productBean->autofill = $product->AutoFill;
    $productBean->from_kashflow = true;
    $productBean->save();
}

/**
 * @param $productBean
 * @param $product
 * @return bool
 */
function checkIfChangedProduct($productBean, $product) {
    $changed = false;
    if($productBean->kashflow_id != $product->id) $changed = true;
    if($productBean->nominal_code != $product->ParentID) $changed = true;
    if($productBean->name != $product->Name) $changed = true;
    if($productBean->part_number != $product->Code) $changed = true;
    if($productBean->description != $product->Description) $changed = true;
    if($productBean->price != $product->Price) $changed = true;
    if($productBean->vat_rate != $product->VatRate) $changed = true;
    if($productBean->cost != $product->WholesalePrice) $changed = true;
    if($productBean->managed != $product->Managed) $changed = true;
    if($productBean->qty_in_stock != $product->QtyInStock) $changed = true;
    if($productBean->stock_warn_qty != $product->StockWarnQty) $changed = true;
    if($productBean->autofill != $product->AutoFill) $changed = true;
    return $changed;
}

/**
 * @param stdClass $invoice
 */
function updateInvoice(stdClass $invoice) {
    global $db;
    $sql = "UPDATE aos_invoices " .
        "SET number = '" . $invoice->InvoiceNumber . "', " .
        "name = '" . $invoice->CustomerName . "', " .
        "invoice_date = '" . substr($invoice->InvoiceDate, 0, 10) . "', " .
        "due_date = '" . substr($invoice->DueDate, 0, 10) . "', " .
        "status = '" . ($invoice->Paid === 1 ? "Paid" : "Unpaid") . "', " .
        "total_amount = '" . $invoice->NetAmount . "', " .
        "billing_account_id = " .
        "(SELECT accounts.id FROM accounts WHERE accounts.kashflow_id = '" . $invoice->CustomerID . "' LIMIT 1), " .
        "tax_amount = '" . $invoice->VATAmount . "', " .
        "amount_paid = '" . $invoice->AmountPaid . "' " .
        "WHERE kashflow_id = '" . $invoice->InvoiceDBID . "'";
    $db->query($sql);
}

/**
 * @param $invoice
 * @return string
 */
function createInvoice($invoice) {

    $invoiceBean = BeanFactory::newBean('AOS_Invoices');
    $invoiceDate = substr($invoice->InvoiceDate, 0, 10);
    $dueDate = substr($invoice->DueDate, 0, 10);
    $accountBean = new Account();
    $accountBean->retrieve_by_string_fields(array('kashflow_id' => $invoice->CustomerID));

    $invoiceBean->number = $invoice->InvoiceNumber;
    $invoiceBean->name = $invoice->CustomerName;
    $invoiceBean->invoice_date = $invoiceDate;
    $invoiceBean->due_date = $dueDate;
    $invoiceBean->status = $invoice->Paid == 1 ? "Paid" : "Unpaid";
    $invoiceBean->billing_account_id = $accountBean->id;
    $invoiceBean->total_amount = $invoice->NetAmount;
    $invoiceBean->tax_amount = $invoice->VATAmount;
    $invoiceBean->amount_paid = $invoice->AmountPaid;
    $invoiceBean->from_kashflow = true;
    $invoiceBean->kashflow_id = $invoice->InvoiceDBID;
    $invoiceBean->save();
    $sql = "UPDATE aos_invoices SET number = '".$invoice->InvoiceNumber."' WHERE id = '".$invoiceBean->id."'";
    $invoiceBean->db->query($sql);
    return $invoiceBean->id;
}

/**
 * @param $invoiceBean
 * @param $invoice
 * @return bool
 */
function checkIfChangedInvoice($invoiceBean, $invoice) {
    $changed = false;
    $accountBean = new Account();
    $accountBean->retrieve_by_string_fields(array('kashflow_id' => $invoice->CustomerID));

    if($invoiceBean->invoice_date."T00:00:00" != $invoice->InvoiceDate) $changed = true;
    if($invoiceBean->due_date."T00:00:00" != $invoice->DueDate) $changed = true;
    if($invoiceBean->billing_account_id != $accountBean->id) $changed = true;
    if($invoiceBean->total_amount != $invoice->NetAmount) $changed = true;
    if($invoiceBean->tax_amount != $invoice->VATAmount) $changed = true;
    if($invoiceBean->amount_paid != $invoice->AmountPaid) $changed = true;
    if(($invoiceBean->status == "Paid" && $invoice->Paid != 1) ||
        ($invoiceBean->status != "Paid" && $invoice->Paid == 1)) $changed = true;
    return $changed;
}

/**
 * @param string $parentId
 * @param stdClass $invoice
 */
function updateLineItems($parentId, $invoice) {
    $existingLineItems = [];
    if(!empty($invoice->Lines)) {
        $lineArray = [];
        if (!empty($invoice->Lines->enc_value->ProductID)) {
            $lineArray[] = $invoice->Lines;
        } else {
            $lineArray = $invoice->Lines->anyType;
        }
        $existingInDb = checkIfKashFlowRecordsExists(
            $lineArray,
            'aos_products_quotes',
            ['LineID' => 'kashflow_id'],
            'enc_value'
        );
        foreach($lineArray as $line) {
            if (empty($line->enc_value)){
                continue;
            }
            $existingLineItems[] = $line->enc_value->LineID;
            if (in_array($line->enc_value->LineID, $existingInDb, false)) {
                if (updateLineItem($line->enc_value, $parentId)) {
                    continue;
                }
            }
            createLineItem($line->enc_value, $parentId);
        }
    }
}

/**
 * @param stdClass $lineItem
 * @param string $parentId
 * @return bool
 */
function updateLineItem($lineItem, $parentId)
{
    global $db;
    $productSql = "SELECT id, name, part_number, nominal_code, kashflow_id " .
        "FROM aos_products " .
        "WHERE nominal_code = '" . $lineItem->ChargeType . "' " .
        "AND kashflow_id = '" . $lineItem->ProductID . "'";
    $productResult = $db->query($productSql);
    $productRow = $db->fetchByAssoc($productResult);
    if (!$productRow) {
        return false;
    }

    $sql = "UPDATE aos_products_quotes " .
        "SET product_qty = '" . $lineItem->Quantity . "', " .
        "item_description = '" . $lineItem->Description . "', " .
        "product_list_price = '" . $lineItem->Rate . "', " .
        "product_unit_price = '" . $lineItem->Rate . "', " .
        "vat = '" . round($lineItem->VatRate, 1) . "', " .
        "vat_amt = '" . round($lineItem->VatRate, 2) . "', " .
        "number = '" . $lineItem->Sort . "', " .
        "product_id = '" . $productRow['id'] . "', " .
        "name = '" . (empty($productRow['name']) ? $lineItem->Description : $productRow['name']) . "', " .
        "part_number = '" . $productRow['part_number'] . "', " .
        "parent_type = 'AOS_Invoices', " .
        "parent_id = '$parentId', " .
        "product_total_price = '" . $lineItem->Rate * $lineItem->Quantity . "' " .
        "WHERE kashflow_id = '" . $lineItem->LineID . "'";
    $db->query($sql);
    return true;
}

/**
 * @param stdClass $values
 * @param string $parentId
 */
function createLineItem($values, $parentId)
{
    $line_item = new AOS_Products_Quotes();
    $line_item->retrieve_by_string_fields(array("kashflow_id" => $values->LineID));
    $product = new AOS_Products();
    $product->retrieve_by_string_fields(array("nominal_code" => $values->ChargeType, "kashflow_id" => $values->ProductID));
    $line_item->product_qty = $values->Quantity;
    $line_item->item_description = $values->Description;
    $line_item->product_list_price = $values->Rate;
    $line_item->product_unit_price = $values->Rate;
    $line_item->vat = round($values->VatRate, 1);
    $line_item->vat_amt = round($values->VatAmount, 2);
    $line_item->number = $values->Sort;
    $line_item->kashflow_id = $values->LineID;
    $line_item->product_id = $product->id;
    $line_item->name = $product->name;
    $line_item->part_number = $product->part_number;
    $line_item->parent_type = "AOS_Invoices";
    $line_item->parent_id = $parentId;
    $line_item->product_total_price = $line_item->product_list_price * $line_item->product_qty;
    if(empty($line_item->name)) $line_item->name = $values->Description;
    if(!empty($line_item->name)){
        $line_item->save();
    }
}