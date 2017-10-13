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
            if(!empty($bean->number)){
                $response = $kashflow->getInvoice($bean->number);
                $parameters['Inv'] = $response->GetInvoiceResult;
                $parameters['Inv']->InvoiceDate = $bean->invoice_date."T00:00:00";
                $parameters['Inv']->DueDate = $bean->due_date."T00:00:00";
                $parameters['Inv']->CustomerID = (int)$accountBean->kashflow_id;
                $parameters['Inv']->Paid = $bean->status == "Paid" ? 1 : 0;
                $parameters['Inv']->NetAmount = !empty($bean->total_amount) ? $bean->total_amount : "0.0000";
                $parameters['Inv']->VATAmount = !empty($bean->tax_amount) ? $bean->tax_amount : "0.0000";
                $parameters['Inv']->AmountPaid = !empty($bean->amount_paid) ? $bean->amount_paid : "0.0000";
                $response = $kashflow->updateInvoice($parameters);
            } else {
                $parameters['Inv'] = array
                (
                    "InvoiceDBID"   => 0,
                    "InvoiceNumber" => !empty($bean->number) ? $bean->number : 0,
                    "InvoiceDate"   => $bean->invoice_date."T00:00:00",
                    "DueDate"       => $bean->due_date."T00:00:00",
                    "CustomerID"    => (int)$accountBean->kashflow_id,
                    "Paid"          => $bean->status == "Paid" ? 1 : 0,
                    "SuppressTotal" => 0,
                    "ProjectID"     => 0,
                    "ExchangeRate"  => "0.0000",
                    "NetAmount"     => !empty($bean->total_amount) ? $bean->total_amount : "0.0000",
                    "VATAmount"     => !empty($bean->tax_amount) ? $bean->tax_amount : "0.0000",
                    "AmountPaid"    => !empty($bean->amount_paid) ? $bean->amount_paid : "0.0000",
                    "UseCustomDeliveryAddress"  => false,
                );
                $response = $kashflow->insertInvoice($parameters);
                if(!empty($response->InsertInvoiceResult)) $bean->number = $response->InsertInvoiceResult;
            }
            if($response->Status == "NO") SugarApplication::appendErrorMessage('LBL_FAILED_TO_SEND');
        }
    }
}