<?php

require_once 'custom/Extension/modules/Schedulers/Ext/ScheduledTasks/KashflowTasks.php';

if (!empty($_REQUEST['forceAll'])) {
    getAllInvoices();
} else {
    getInvoices();
}

echo json_encode(['result' => 'ok']);