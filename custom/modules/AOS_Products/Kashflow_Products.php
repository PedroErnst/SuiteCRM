<?php
require_once 'custom/include/Kashflow/Kashflow.php';
class Kashflow_Products {

    /**
     * @param $bean
     * @param $event
     * @param $arguments
     */
    function addOrUpdateSubProduct($bean, $event, $arguments)
    {
        global $sugar_config;
        if ($sugar_config['kashflow_api']['send_products'] == 1 &&
            (($sugar_config['kashflow_api']['send_products_option'] == 'modified' && $bean->date_entered != $bean->date_modified) ||
             ($sugar_config['kashflow_api']['send_products_option'] == 'new' && $bean->date_entered == $bean->date_modified) ||
              $sugar_config['kashflow_api']['send_products_option'] == 'all')) {
            $kashflow = new Kashflow();
            !empty($bean->kashflow_id) ? $kid = $bean->kashflow_id : $kid = 0;
            $parameters['sp'] = array
            (
                "id"            => $kid,
                "ParentID"      => $bean->nominal_code,
                "Name"          => $bean->name,
                "Code"          => $bean->part_number,
                "Description"   => $bean->description,
                "Price"         => $bean->price,
                "VatRate"       => $bean->vat_rate,
                "WholesalePrice"=> $bean->cost,
                "Managed"       => $bean->managed,
                "QtyInStock"    => $bean->qty_in_stock,
                "StockWarnQty"  => $bean->stock_warn_qty,
                "AutoFill"      => $bean->autofill
            );
            $response = $kashflow->addOrUpdateSubProduct($parameters);
            if(!empty($response->AddOrUpdateSubProductResult)) $bean->kashflow_id = $response->AddOrUpdateSubProductResult;

        }
    }
}