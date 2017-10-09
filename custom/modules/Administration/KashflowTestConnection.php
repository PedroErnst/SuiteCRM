<?php

require_once 'custom/include/Kashflow/Kashflow.php';
$kashflow = new Kashflow($_POST['kashflow_api']);
$response = $kashflow->checkLoginDetails();
$list = array();

if ($response->Status !== "OK") {
    echo false;
} else {
    echo true;
}