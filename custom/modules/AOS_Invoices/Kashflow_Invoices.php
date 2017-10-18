<?php
require_once 'custom/include/Kashflow/Kashflow.php';
require_once 'custom/include/Kashflow/Kashflow_Customer_Hooks.php';
class Kashflow_Invoices {

    /**
     * @param $bean
     * @param $event
     * @param $arguments
     */
    function addOrUpdateInvoice($bean, $event, $arguments)
    {
        global $sugar_config;
        if ($sugar_config['kashflow_api']['send_invoices'] == 1 && $bean->from_kashflow == false &&
            (($sugar_config['kashflow_api']['send_invoices_option'] == 'modified' && $bean->date_entered != $bean->date_modified) ||
             ($sugar_config['kashflow_api']['send_invoices_option'] == 'new' && $bean->date_entered == $bean->date_modified) ||
              $sugar_config['kashflow_api']['send_invoices_option'] == 'all')) {
            $kashflow = new Kashflow();
            if(!empty($bean->billing_account_id)) {
                $accountBean = BeanFactory::getBean("Accounts", $bean->billing_account_id);
                $kashflowAccount = new Kashflow_Customer_Hooks();
                if(!empty($bean->billing_contact_id)) {
                    $contactBean = BeanFactory::getBean("Contacts", $bean->billing_contact_id);
                    $kashflowAccount->sendCustomerDetails($accountBean, $kashflow, $contactBean);
                } else {
                    if($accountBean->load_relationship('contacts')) {
                        $contactBeans = $accountBean->get_linked_beans('contacts','Contact');
                        foreach($contactBeans as $contact) {
                            if($contact->billing_contact == true) {
                                $billing_contact = $contact;
                                break;
                            }
                        }
                    }
                    if (isset($billing_contact)) $kashflowAccount->sendCustomerDetails($accountBean, $kashflow, $billing_contact);
                    else $kashflowAccount->sendCustomerDetails($accountBean, $kashflow);
                }
            }
            $response = $kashflow->getInvoice($bean->number);
            if($response->GetInvoiceResult->InvoiceDBID != 0){

                // HANDLE EXISTING LINE ITEMS
                if(!empty($response->GetInvoiceResult->Lines)) {
                    foreach($response->GetInvoiceResult->Lines as $row) {
                        if($row->enc_value->LineID != 0) {
                            if ($row->enc_value->LineID) {
                                $line_item = new AOS_Products_Quotes();
                                $line_item->retrieve_by_string_fields(array('kashflow_id' => $row->LineID));
                            }
                            if ($row->enc_value->ProductID) {
                                $product = new AOS_Products();
                                $product->retrieve_by_string_fields(array('kashflow_id' => $row->ProductID));
                            }
                            if($line_item->deleted != 1) {
                                $line = array(
                                    "LineID"           => !empty($line_item->kashflow_id) ? $line_item->kashflow_id : 0,
                                    "Quantity"         => !empty($line_item->product_qty) ? $line_item->product_qty : $row->Quantity,
                                    "Description"      => !empty($line_item->item_description) ? $line_item->item_description : $row->Description,
                                    "Rate"             => !empty($line_item->product_unit_price) ? $line_item->product_unit_price : $row->Rate,
                                    "ChargeType"       => !empty($product->nominal_code) ? (int)$product->nominal_code : $row->ChargeType,
                                    "VatAmount"        => !empty($line_item->product_vat_amt) ? $line_item->product_vat_amt : $row->VatAmount,
                                    "VatRate"          => !empty($line_item->product_vat) ? $line_item->product_vat : $row->VatRate,
                                    "Sort"             => !empty($line_item->number) ? $line_item->number : $row->Sort,
                                    "ProductID"        => !empty($product->kashflow_id) ? (int)$product->kashflow_id : $row->ProductID,
                                    "ValuesInCurrency" => 0,
                                    "ProjID"           => 0,
                                );
                                if($line->LineID != 0) $lines[] = new SoapVar($line, 0, "InvoiceLine", "KashFlow");
                            } elseif($line_item->deleted == 1) {
                                $deleteParams['LineID'] = $line_item->kashflow_id;
                                $deleteParams['InvoiceNumber'] = $bean->number;
                                $kashflow->deleteInvoiceLine($deleteParams);
                            }
                        }
                    }
                }

                $parameters['Inv'] = $response->GetInvoiceResult;
                $parameters['Inv']->InvoiceDate = $bean->invoice_date."T00:00:00";
                $parameters['Inv']->DueDate = $bean->due_date."T00:00:00";
                $parameters['Inv']->CustomerID = (int)$accountBean->kashflow_id;
                $parameters['Inv']->Paid = $bean->status == "Paid" ? 1 : 0;
                if (!empty($lines)) $parameters['Inv']->Lines = $lines; else $parameters['Inv']->Lines = array();
                $parameters['Inv']->NetAmount = !empty($bean->total_amount) ? $bean->total_amount : "0.0000";
                $parameters['Inv']->VATAmount = !empty($bean->tax_amount) ? $bean->tax_amount : "0.0000";
                $parameters['Inv']->AmountPaid = !empty($bean->amount_paid) ? $bean->amount_paid : "0.0000";
                $response = $kashflow->updateInvoice($parameters);
            } else {
                // NEW INVOICE - PREP LINE ITEMS
                $bean->load_relationships();
                if($bean->aos_products_quotes->getBeans()) {
                    foreach ($bean->aos_products_quotes->beans as $line_item) {
                        if ($line_item->product_id) {
                            $product = new AOS_Products();
                            $product->retrieve_by_string_fields(array('id' => $line_item->product_id));
                        }
                        $line = array(
                            "LineID"           => 0,
                            "Quantity"         => $line_item->product_qty,
                            "Description"      => !empty($line_item->item_description) ? $line_item->item_description : "",
                            "Rate"             => $line_item->product_unit_price,
                            "ChargeType"       => !empty($product->nominal_code) ? (int)$product->nominal_code : 0,
                            "VatAmount"        => !empty($line_item->product_vat_amt) ? $line_item->product_vat_amt : 0,
                            "VatRate"          => !empty($line_item->product_vat) ? $line_item->product_vat : 0,
                            "Sort"             => $line_item->number,
                            "ProductID"        => !empty($product->kashflow_id) ? (int)$product->kashflow_id : 0,
                            "ValuesInCurrency" => 0,
                            "ProjID"           => 0,
                        );
                        $lines[] = new SoapVar($line, 0, "InvoiceLine", "KashFlow");
                    }
                }
                $parameters['Inv'] = array
                (
                    "InvoiceDBID"   => 0,
                    "InvoiceNumber" => 0,
                    "InvoiceDate"   => $bean->invoice_date."T00:00:00",
                    "DueDate"       => $bean->due_date."T00:00:00",
                    "CustomerID"    => (int)$accountBean->kashflow_id,
                    "Paid"          => $bean->status == "Paid" ? 1 : 0,
                    "SuppressTotal" => 0,
                    "ProjectID"     => 0,
                    "ExchangeRate"  => "0.0000",
                    "Lines"         => !empty($lines) ? $lines : array(),
                    "NetAmount"     => !empty($bean->total_amount) ? $bean->total_amount : "0.0000",
                    "VATAmount"     => !empty($bean->tax_amount) ? $bean->tax_amount : "0.0000",
                    "AmountPaid"    => !empty($bean->amount_paid) ? $bean->amount_paid : "0.0000",
                    "UseCustomDeliveryAddress"  => false,
                );
                $response = $kashflow->insertInvoice($parameters);
                if(!empty($response->InsertInvoiceResult)){
                    $sql = "UPDATE aos_invoices SET number = '".$response->InsertInvoiceResult."' WHERE id = '".$bean->id."'";
                    $bean->db->query($sql);
                }
            }
            if($response->Status == "NO") SugarApplication::appendErrorMessage('LBL_FAILED_TO_SEND');
        }
    }
}