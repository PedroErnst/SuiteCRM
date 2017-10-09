<?php

require_once 'custom/include/Kashflow/Kashflow.php';
$kashflow = new Kashflow($_POST['kashflow_api']);
$response = $kashflow->getNominalCodes();
$list = array();

if ($response->Status !== "OK") {
    echo false;
} else {
    foreach($response->GetNominalCodesResult->NominalCode as $nominal) {
        $list['kashflow_nominal_codes'][$nominal->id] = $nominal->Name;
    }
    save_custom_app_list_strings_kashflow($list, "en_us");
    echo true;
}

function save_custom_app_list_strings_kashflow(&$app_list_strings, $language)
{
    $return_value = false;
    $dirname = 'custom/Extension/application/Ext/Language';

    $dir_exists = is_dir($dirname);

    if(!$dir_exists) {
        sugar_mkdir($dirname, null, true);
    }

    $dir_exists = is_dir($dirname);

    if($dir_exists) {
        $filename = "$dirname/$language.KashflowNominalCodes.php";
        $handle = @sugar_fopen($filename, 'wt');

        if($handle) {
            $contents =create_dropdown_lang_contents($app_list_strings, $language);
            if(fwrite($handle, $contents)) {
                $return_value = true;
                $GLOBALS['log']->info("Successful write to: $filename");
            }
            fclose($handle);
        } else {
            $GLOBALS['log']->info("Unable to write edited language pak to file: $filename");
        }
    }
    else {
        $GLOBALS['log']->info("Unable to create dir: $dirname");
    }
    if($return_value) {
        $cache_key = 'app_list_strings.'.$language;
        sugar_cache_clear($cache_key);
    }
    return $return_value;

}

function create_dropdown_lang_contents(&$the_array, $language)
{
    $contents = "<?php\n" .
                '// ' . date('Y-m-d H:i:s') . "\n" .
                "// Language: $language\n\n" .
                '$app_list_strings = ' .
                var_export($the_array, true) .
                ";\n?>";

    return $contents;
}