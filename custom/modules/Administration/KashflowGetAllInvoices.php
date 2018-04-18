<?php

require_once 'custom/Extension/modules/Schedulers/Ext/ScheduledTasks/KashflowTasks.php';

if (!empty($_REQUEST['forceAll'])) {
    if (!empty($_REQUEST['fromDate'])) {
        getAllInvoices($_REQUEST['fromDate']);
    } else {
        getAllInvoices();
    }
} else {
    getInvoices();
}

echo json_encode(['result' => 'ok']);