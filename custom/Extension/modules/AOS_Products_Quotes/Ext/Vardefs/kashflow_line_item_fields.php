<?php

$dictionary['AOS_Products_Quotes']['fields']['kashflow_id'] = array(
    'name' => 'kashflow_id',
    'vname' => 'LBL_KASHFLOW_ID',
    'type' => 'id',
    'len' => 10,
    'reportable' => false,
    'source' => 'db',
    'readonly' => true
);

$dictionary['AOS_Products_Quotes']['fields']['from_kashflow'] = array(
    'name' => 'from_kashflow',
    'vname' => 'LBL_FROM_KASHFLOW',
    'type' => 'bool',
    'default' => false,
    'reportable' => false,
    'source' => 'non-db',
);
