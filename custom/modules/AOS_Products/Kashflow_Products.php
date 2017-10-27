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
        global $sugar_config, $app_strings;
        if ($sugar_config['kashflow_api']['send_products'] == 1 && $bean->from_kashflow == false &&
            (($sugar_config['kashflow_api']['send_products_option'] == 'modified' && $bean->date_entered != $bean->date_modified) ||
             ($sugar_config['kashflow_api']['send_products_option'] == 'new' && $bean->date_entered == $bean->date_modified) ||
              $sugar_config['kashflow_api']['send_products_option'] == 'all')) {
            $kashflow = new Kashflow();
            $parameters['sp'] = array
            (
                "id"            => !empty($bean->kashflow_id) ? $bean->kashflow_id : 0,
                "ParentID"      => $bean->nominal_code,
                "Name"          => $bean->name,
                "Code"          => $bean->part_number,
                "Description"   => $bean->description,
                "Price"         => $bean->price,
                "VatRate"       => !empty($bean->vat_rate) ? $bean->vat_rate : "0.0000",
                "WholesalePrice"=> !empty($bean->cost) ? $bean->cost : "0.0000",
                "Managed"       => !empty($bean->managed) ? $bean->managed : 0,
                "QtyInStock"    => !empty($bean->qty_in_stock) ? $bean->qty_in_stock : 0,
                "StockWarnQty"  => !empty($bean->stock_warn_qty) ? $bean->stock_warn_qty : 0,
                "AutoFill"      => $bean->autofill == true ? 1 : 0
            );
            if(!empty($bean->nominal_code) && !empty($bean->name) && !empty($bean->part_number)) $response = $kashflow->addOrUpdateSubProduct($parameters);
            else SugarApplication::appendErrorMessage($app_strings['LBL_FAILED_KASHFLOW_PRODUCTS']);
            if(!empty($response->AddOrUpdateSubProductResult)) $bean->kashflow_id = $response->AddOrUpdateSubProductResult;
            if($response->Status == "NO") SugarApplication::appendErrorMessage($app_strings['LBL_FAILED_KASHFLOW_PRODUCTS']);
        }
    }
}