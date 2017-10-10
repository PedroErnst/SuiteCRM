<?php
require_once 'custom/include/Kashflow/Kashflow.php';
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
            $line = array
            (
                "LineID"      => 0,
                "Quantity"    => 1,
                "Description" => "description",
                "Rate"        => 9,
                "ChargeType"  => 0,
                "VatAmount"   => 0,
                "VatRate"     => 0,
                "Sort"        => 1,
                "ProductID"   => 0,
                "ProjID"      => 0,
            );

            $lines[] = new SoapVar($line,0,"InvoiceLine","KashFlow");

            $parameters['Inv'] = array
            (
                "InvoiceDBID"   => 91828459,
                "InvoiceNumber" => 3,
                "InvoiceDate"   => "2017-09-20T00:00:00",
                "DueDate"       => "2017-09-30T00:00:00",
                "CustomerID"    => 72177290,
                "Paid"          => 0,
                "SuppressTotal" => 0,
                "ProjectID"     => 0,
                "ExchangeRate"  => "0.0000",
                "Lines"         => $lines,
                "NetAmount"     => "0.0000",
                "VATAmount"     => "0.0000",
                "AmountPaid"    => "0.0000",
                "UseCustomDeliveryAddress"  => false,
            );
            $response = $kashflow->insertInvoice($parameters);
            if(!empty($response->InsertInvoiceResult)) $bean->kashflow_id = $response->InsertInvoiceResult;
            if($response->Status == "NO") SugarApplication::appendErrorMessage('LBL_FAILED_TO_SEND');
        }
    }
}