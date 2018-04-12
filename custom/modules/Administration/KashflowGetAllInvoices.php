<?php

require_once 'custom/include/Kashflow/Kashflow.php';
global $timedate;

// Set script timeout to 10 hour
ini_set("max_execution_time", "36000");

$interval = DateInterval::createFromDateString('1 month');
$start = $timedate->fromDbDate('2000-01-01');
$end = $timedate->getNow();

$kashflow = new Kashflow($_POST['kashflow_api']);
while ($start < $end) {
    $response = $kashflow->getInvoicesByDateRange(
        str_replace(' ', 'T', $timedate->asDb($start)),
        str_replace(' ', 'T', $timedate->asDb($start->add($interval)))
    );
    saveInvoiceResponse($response, 0);
}

/**
 * @param $response
 * @param int $maxNewRecords
 * @return bool
 */
function saveInvoiceResponse($response, $maxNewRecords = 50) {
    if ($response->Status !== "OK") {
        return false;
    }

    $invoiceArray = [];
    if (!empty($response->GetInvoicesByDateRangeResult->Invoice->InvoiceDBID)) {
        $invoiceArray[] = $response->GetInvoicesByDateRangeResult->Invoice;
    } else {
        $invoiceArray = $response->GetInvoicesByDateRangeResult->Invoice;
    }

    $fieldsToCheck = [
        'InvoiceDBID' => 'kashflow_id',
        'InvoiceNumber' => 'number',
    ];
    $existingInDb = checkIfKashFlowRecordsExists($invoiceArray, 'aos_invoices', $fieldsToCheck);
    $newlyCreated = 0;
    foreach($invoiceArray as $invoice) {
        $beanId = array_search($invoice->InvoiceDBID, $existingInDb);
        if ($beanId !== false) {
            updateInvoice($invoice);
        }
        else {
            $beanId = createInvoice($invoice);
            $newlyCreated++;
        }
        updateLineItems($beanId, $invoice);
        if ($maxNewRecords && $newlyCreated > $maxNewRecords) {
            break;
        }
    }
    return true;
}

/**
 * @param array $recordArray
 * @param string $table
 * @param array $fieldsToCheck
 * @param string $subFieldName
 * @return array
 */
function checkIfKashFlowRecordsExists($recordArray, $table, $fieldsToCheck, $subFieldName = null)
{
    global $db;
    $existing = [];
    $arrayFieldsToCheck = [];
    foreach ($fieldsToCheck as $recordName => $dbName) {
        $arrayFieldsToCheck[$dbName] = [];
    }
    foreach ($recordArray as $record) {
        foreach ($fieldsToCheck as $recordName => $dbName) {
            if ($subFieldName) {
                $arrayFieldsToCheck[$dbName][] = $record->$subFieldName->$recordName;
                continue;
            }
            $arrayFieldsToCheck[$dbName][] = $record->$recordName;
        }
    }
    $sql = "SELECT id," . implode(',', $fieldsToCheck) . " FROM $table WHERE ";
    foreach ($arrayFieldsToCheck as $fieldName => $values) {
        $sql .= "OR $fieldName IN (" . implode(',', $values) . ") ";
    }
    $sql = str_replace('WHERE OR', 'WHERE', $sql);
    $result = $db->query($sql);
    $fieldToReturn = array_shift($fieldsToCheck);
    while ($row = $db->fetchByAssoc($result)) {
        $existing[$row['id']] = $row[$fieldToReturn];
    }

    return $existing;
}
