<?php

$job_strings[] = 'getProducts';

require_once 'custom/include/Kashflow/Kashflow.php';

function getProducts() {

    $kashflow = new Kashflow();
    $response = $kashflow->getSubProducts();
    if ($response->Status == "OK") {
        return true;
    } else {
        return false;
    }
}