<?php

$dictionary['Contact']['fields']['from_kashflow'] = array(
    'name' => 'from_kashflow',
    'vname' => 'LBL_FROM_KASHFLOW',
    'type' => 'bool',
    'default' => false,
    'reportable' => false,
    'source' => 'non-db',
);

$dictionary['Contact']['fields']['billing_contact'] = array(
    'name' => 'billing_contact',
    'vname' => 'LBL_BILLING_CONTACT',
    'type' => 'bool',
    'default' => false,
    'reportable' => false,
    'source' => 'db',
);