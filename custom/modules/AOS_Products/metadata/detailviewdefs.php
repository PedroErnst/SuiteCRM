<?php
$module_name = 'AOS_Products';
$viewdefs [$module_name] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
        ),
      ),
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
      'tabDefs' => 
      array (
        'DEFAULT' => 
        array (
          'newTab' => false,
          'panelDefault' => 'expanded',
        ),
        'LBL_EDITVIEW_PANEL1' => 
        array (
          'newTab' => false,
          'panelDefault' => 'expanded',
        ),
      ),
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'label' => 'LBL_NAME',
          ),
          1 => 
          array (
            'name' => 'part_number',
            'label' => 'LBL_PART_NUMBER',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'aos_product_category_name',
            'label' => 'LBL_AOS_PRODUCT_CATEGORYS_NAME',
          ),
          1 => 
          array (
            'name' => 'type',
            'label' => 'LBL_TYPE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'currency_id',
            'studio' => 'visible',
            'label' => 'LBL_CURRENCY',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'cost',
            'label' => 'LBL_COST',
          ),
          1 => 
          array (
            'name' => 'price',
            'label' => 'LBL_PRICE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'contact',
            'label' => 'LBL_CONTACT',
          ),
          1 => 
          array (
            'name' => 'url',
            'label' => 'LBL_URL',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'product_image',
            'label' => 'LBL_PRODUCT_IMAGE',
            'customCode' => '<img src="{$fields.product_image.value}"/>',
          ),
        ),
      ),
      'lbl_editview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'kashflow_id',
            'label' => 'LBL_KASHFLOW_ID',
          ),
          1 => 
          array (
            'name' => 'nominal_code',
            'label' => 'LBL_NOMINAL_CODE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'vat_rate',
            'label' => 'LBL_VAT_RATE',
          ),
          1 => 
          array (
            'name' => 'managed',
            'label' => 'LBL_MANAGED',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'qty_in_stock',
            'label' => 'LBL_QTY_IN_STOCK',
          ),
          1 => 
          array (
            'name' => 'stock_warn_qty',
            'label' => 'LBL_STOCK_WARN_QTY',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'autofill',
            'label' => 'LBL_AUTOFILL',
          ),
          1 => '',
        ),
      ),
    ),
  ),
);
$viewdefs['AOS_Products']['DetailView']['templateMeta'] = array (
  'form' => 
  array (
    'buttons' => 
    array (
      0 => 'EDIT',
      1 => 'DUPLICATE',
      2 => 'DELETE',
    ),
  ),
  'maxColumns' => '2',
  'widths' => 
  array (
    0 => 
    array (
      'label' => '10',
      'field' => '30',
    ),
    1 => 
    array (
      'label' => '10',
      'field' => '30',
    ),
  ),
  'useTabs' => true,
  'tabDefs' => 
  array (
    'DEFAULT' => 
    array (
      'newTab' => true,
      'panelDefault' => 'expanded',
    ),
  ),
);
?>
