<?php
$admin_options_defs = array();
$admin_options_defs['Administration']['Kashflow'] = array(
    'Administration',
    'LBL_KASHFLOW_CONFIGURATION',
    'LBL_KASHFLOW_CONFIGURATION_DESCRIPTION',
    './index.php?module=Administration&action=KashflowConfiguration'
);

$admin_group_header[]= array(
    'LBL_KASHFLOW_GROUP_TITLE',
    '',
    false,
    $admin_options_defs,
);