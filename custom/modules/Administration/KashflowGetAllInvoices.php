<?php

require_once 'custom/Extension/modules/Schedulers/Ext/ScheduledTasks/KashflowTasks.php';

$interval = !empty($_REQUEST['interval']) ? $_REQUEST['interval'] : false;
$maxNew = !empty($_REQUEST['maxNew']) ? $_REQUEST['maxNew'] : 500;

getInvoices($interval, $maxNew);

echo json_encode(['result' => 'ok']);