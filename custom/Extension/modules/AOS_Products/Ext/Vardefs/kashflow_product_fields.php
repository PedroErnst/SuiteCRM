<?php

$dictionary['AOS_Products']['fields']['kashflow_id'] = array(
    'name' => 'kashflow_id',
    'vname' => 'LBL_KASHFLOW_ID',
    'type' => 'id',
    'len' => 10,
    'reportable' => false,
    'source' => 'db',
    'readonly' => true
);

$dictionary['AOS_Products']['fields']['nominal_code'] = array(
    'name' => 'nominal_code',
    'vname' => 'LBL_NOMINAL_CODE',
    'type' => 'enum',
    'reportable' => false,
    'source' => 'db',
    'options' => 'kashflow_nominal_codes'
);

$dictionary['AOS_Products']['fields']['vat_rate'] = array(
    'name' => 'vat_rate',
    'vname' => 'LBL_VAT_RATE',
    'type' => 'decimal',
    'len' => 18,
    'reportable' => false,
    'source' => 'db',
    'precision' => 4,
);

$dictionary['AOS_Products']['fields']['managed'] = array(
    'name' => 'managed',
    'vname' => 'LBL_MANAGED',
    'type' => 'int',
    'len' => 10,
    'reportable' => false,
    'source' => 'db',
);

$dictionary['AOS_Products']['fields']['qty_in_stock'] = array(
    'name' => 'qty_in_stock',
    'vname' => 'LBL_QTY_IN_STOCK',
    'type' => 'int',
    'len' => 10,
    'reportable' => false,
    'source' => 'db',
);

$dictionary['AOS_Products']['fields']['stock_warn_qty'] = array(
    'name' => 'stock_warn_qty',
    'vname' => 'LBL_STOCK_WARN_QTY',
    'type' => 'int',
    'len' => 10,
    'reportable' => false,
    'source' => 'db',
);

$dictionary['AOS_Products']['fields']['autofill'] = array(
    'name' => 'autofill',
    'vname' => 'LBL_AUTOFILL',
    'type' => 'bool',
    'reportable' => false,
    'source' => 'db',
);
