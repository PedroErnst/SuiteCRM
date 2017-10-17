<?php

$job_strings[] = 'getProducts';
$job_strings[] = 'getInvoices';

require_once 'custom/include/Kashflow/Kashflow.php';
require_once 'modules/AOS_Products/AOS_Products.php';
require_once 'modules/AOS_Invoices/AOS_Invoices.php';
require_once 'modules/Accounts/Account.php';

function getProducts() {
    global $app_list_strings;

    $kashflow = new Kashflow();
    foreach($app_list_strings['kashflow_nominal_codes'] as $code => $label) {
        $response = $kashflow->getSubProducts($code);
        if ($response->Status == "OK") {
            $productsArray = "";
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
        }
    }
}

function getInvoices() {

    $kashflow = new Kashflow();
    $response = $kashflow->getInvoicesByDateRange();
    if ($response->Status == "OK") {
        foreach($response->GetInvoicesByDateRangeResult->Invoice as $invoice) {
            // Find based on Kashflow ID
            $invoiceBean = new AOS_Invoices();
            $invoiceBean->retrieve_by_string_fields(array('number' => $invoice->InvoiceNumber));
            if(!empty($invoiceBean->id)) {
                if (checkIfChangedInvoice($invoiceBean, $invoice) == true) updateInvoice($invoiceBean, $invoice);
            } else updateInvoice($invoiceBean, $invoice);
        }
    }
}

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

function updateInvoice($invoiceBean, $invoice) {

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
    $invoiceBean->save();
}

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