<?php

$job_strings[] = 'getProducts';

require_once 'custom/include/Kashflow/Kashflow.php';
require_once 'modules/AOS_Products/AOS_Products.php';

function getProducts() {
    global $app_list_strings;

    $kashflow = new Kashflow();
    foreach($app_list_strings['kashflow_nominal_codes'] as $code => $label) {
        $response = $kashflow->getSubProducts($code);
        if ($response->Status == "OK") {
            foreach($response->GetSubProductsResult->SubProduct as $product) {
                // Find based on Kashflow ID
                $productBean = new AOS_Products();
                $productBean->retrieve_by_string_fields(array('kashflow_id' => $product->id));
                if(!empty($productBean->id)) {
                    if (checkIfChanged($productBean, $product) == true) updateProduct($productBean, $product);
                } else updateProduct($productBean, $product);
            }
        }
    }
}

function getInvoices() {

    $kashflow = new Kashflow();
    $response = $kashflow->getInvoicesByDateRange();
    if ($response->Status == "OK") {
        foreach($response->GetSubProductsResult->SubProduct as $product) {
            // Find based on Kashflow ID
            $productBean = new AOS_Products();
            $productBean->retrieve_by_string_fields(array('kashflow_id' => $product->id));
            if(!empty($productBean->id)) {
                if (checkIfChanged($productBean, $product) == true) updateProduct($productBean, $product);
            } else updateProduct($productBean, $product);
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

function checkIfChanged($productBean, $product) {
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