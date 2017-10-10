<?php
$dictionary['Account']['fields']['kashflow_id'] = array(
    'name' => 'kashflow_id',
    'vname' => 'LBL_KASHFLOW_ID',
    'type' => 'id',
    'len' => 18,
    'reportable' => false,
    'source' => 'db',
    'precision' => 4,
);

$dictionary['Account']['fields']['kashflow_code'] = array(
    'name' => 'kashflow_code',
    'vname' => 'LBL_KASHFLOW_CODE',
    'type' => 'varchar',
    'len' => 10,
    'reportable' => false,
    'source' => 'db',
);

$dictionary['Account']['fields']['from_kashflow'] = array(
    'name' => 'from_kashflow',
    'vname' => 'LBL_FROM_KASHFLOW',
    'type' => 'bool',
    'default' => false,
    'reportable' => false,
    'source' => 'non-db',
);