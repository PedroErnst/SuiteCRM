<?php

require_once 'modules/Configurator/Configurator.php';

class CustomConfigurator extends Configurator
{
    var $allow_undefined = [
        'stack_trace_errors',
        'export_delimiter',
        'use_real_names',
        'developerMode',
        'default_module_favicon',
        'authenticationClass',
        'SAML_loginurl',
        'SAML_X509Cert',
        'dashlet_auto_refresh_min',
        'show_download_tab',
        'enable_action_menu',
        'enable_line_editing_list',
        'enable_line_editing_detail',
        'hide_subpanels',
        'kashflow_api'
    ];
}