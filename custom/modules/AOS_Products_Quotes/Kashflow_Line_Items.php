<?php
require_once 'custom/include/Kashflow/Kashflow.php';
require_once 'custom/include/Kashflow/Kashflow_Customer_Hooks.php';
require_once 'modules/AOS_Products/AOS_Products.php';
require_once 'modules/AOS_Invoices/AOS_Invoices.php';
class Kashflow_Line_Items{

    /**
     * @param $bean
     * @param $event
     * @param $arguments
     */
    function addOrUpdateInvoiceLine($bean, $event, $arguments)
    {
        global $sugar_config;
        if ($sugar_config['kashflow_api']['send_invoices'] == 1 && $bean->from_kashflow == false &&
            (($sugar_config['kashflow_api']['send_invoices_option'] == 'modified' && $bean->date_entered != $bean->date_modified) ||
             ($sugar_config['kashflow_api']['send_invoices_option'] == 'new' && $bean->date_entered == $bean->date_modified) ||
              $sugar_config['kashflow_api']['send_invoices_option'] == 'all')) {
            $kashflow = new Kashflow();

            if($bean->product_id) {
                $product = new AOS_Products();
                $product->retrieve_by_string_fields(array('id' => $bean->product_id));
            }
            $invoice = new AOS_Invoices();
            $invoice->retrieve_by_string_fields(array('id' => $bean->parent_id));

            $sql = "SELECT kashflow_id FROM aos_products_quotes WHERE id = '".$bean->id."'";
            $result = $bean->db->query($sql);
            $row = mysqli_fetch_assoc($result);

            $parameters['InvoiceNumber'] = $invoice->number;
            $parameters['InvLine'] = array (
                "LineID" => !empty($row['kashflow_id']) ? (int)$row['kashflow_id'] : 0,
                "Quantity" => $bean->product_qty,
                "Description" => !empty($bean->product_description) ? $bean->product_description : "",
                "Rate" => $bean->product_unit_price,
                "ChargeType" => !empty($product->nominal_code) ? (int)$product->nominal_code : 0,
                "VatAmount" => !empty($bean->product_vat_amt) ? $bean->product_vat_amt : 0,
                "VatRate" => !empty($bean->product_vat) ? $bean->product_vat : 0,
                "Sort" => $bean->number,
                "ProductID" => !empty($product->kashflow_id) ? (int)$product->kashflow_id : 0,
                "ValuesInCurrency" => 0,
                "ProjID" => 0,
            );

            $response = $kashflow->insertInvoiceLine($parameters);
            if(!empty($response->InsertInvoiceLineWithInvoiceNumberResult)) $bean->kashflow_id = $response->InsertInvoiceLineWithInvoiceNumberResult;
        }
    }
}