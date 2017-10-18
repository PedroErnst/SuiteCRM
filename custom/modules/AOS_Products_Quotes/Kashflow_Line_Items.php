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
              $sugar_config['kashflow_api']['send_invoices_option'] == 'all' && $bean->deleted == 0)) {
            $kashflow = new Kashflow();

            if (empty($bean->kashflow_id)) {
                if($bean->product_id) {
                    $product = new AOS_Products();
                    $product->retrieve_by_string_fields(array('id' => $bean->product_id));
                }
                $invoice = new AOS_Invoices();
                $invoice->retrieve_by_string_fields(array('id' => $bean->parent_id));

                $line['InvoiceNumber'] = $invoice->number;
                $line['InvLine'] = array(
                    "LineID"           => 0,
                    "Quantity"         => $bean->product_qty,
                    "Description"      => !empty($bean->item_description) ? $bean->item_description : "",
                    "Rate"             => $bean->product_unit_price,
                    "ChargeType"       => !empty($product->nominal_code) ? (int)$product->nominal_code : 0,
                    "VatAmount"        => !empty($bean->product_vat_amt) ? $bean->product_vat_amt : 0,
                    "VatRate"          => !empty($bean->product_vat) ? $bean->product_vat : 0,
                    "Sort"             => $bean->number,
                    "ProductID"        => !empty($product->kashflow_id) ? (int)$product->kashflow_id : 0,
                    "ValuesInCurrency" => 0,
                    "ProjID"           => 0,
                );
                $response = $kashflow->insertInvoiceLine($line);
                if (!empty($response->InsertInvoiceLineWithInvoiceNumberResult)) {
                    $sql =
                        "UPDATE aos_products_quotes SET kashflow_id = '" .
                        $response->InsertInvoiceLineWithInvoiceNumberResult .
                        "' WHERE id = '" .
                        $bean->id .
                        "'";
                    $bean->db->query($sql);
                }
            }
        }
    }
}