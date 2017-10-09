<form id="ConfigureSettings" name="ConfigureSettings" enctype='multipart/form-data' method="POST"
      action="index.php?module=Administration&action=KashflowConfiguration&do=save">

    <span class='error'>{$error.main}</span>

    <table width="100%" cellpadding="0" cellspacing="1" border="0" class="actionsContainer">
        <tr>
            <td>
                {$BUTTONS}
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
        <tr><th align="left" scope="row" colspan="4"><h4>{$MOD.LBL_KASHFLOW_GROUP_TITLE}</h4></th>
        <tr>
            <td scope="row" width="200">
                <span>{$MOD.LBL_KASHFLOW_API_USERNAME}</span>
            </td>
            <td>
                <input type='text' size='50' class="kashflow_api" name='kashflow_api[username]' value='{$config.kashflow_api.username}'>
            </td>
            <td scope="row" width="200">
                <span>{$MOD.LBL_KASHFLOW_API_PASSWORD}</span>
            </td>
            <td>
                <input type='password' size='50' class="kashflow_api" name='kashflow_api[password]' value='{$config.kashflow_api.password}'>
            </td>
        </tr>

        <tr>
            <td scope="row" width="200">
                <button class="button primary" id='kashflowTestConnection' name='kashflowTestConnection'>{$MOD.LBL_KASHFLOW_TEST_CONNECTION}</button>
            </td>
            <td scope="row" width="200">
                <button class="button primary" id='kashflowGetNominalCodes' name='kashflowGetNominalCodes'>{$MOD.LBL_KASHFLOW_GET_NOMINAL_CODES}</button>
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
        <tr>
            <td scope="row" width="2">
                {if $config.kashflow_api.send_products == "1"}
                {assign var="checked" value='checked="checked"'}
                {else}
                {assign var="checked" value=""}
                {/if}
                <input type="hidden" value ='0' name="kashflow_api[send_products]">
                <input type="checkbox" class="kashflow_api" id="kashflow_api[send_products]" name="kashflow_api[send_products]" value="1" {$checked} >
                <span> {$MOD.LBL_KASHFLOW_SEND_PRODUCTS}</span>
            </td>
            <td scope="row" width="2">
                <select class="kashflow_api" id="kashflow_api[send_products_option]" name="kashflow_api[send_products_option]">
                    <option label="New Records" value="new" {if $config.kashflow_api.send_products_option == "new"}selected="selected"{/if}>New Records</option>
                    <option label="Modified Records" value="modified" {if $config.kashflow_api.send_products_option == "modified"}selected="selected"{/if}>Modified Records</option>
                    <option label="All Existing" value="all" {if $config.kashflow_api.send_products_option == "all"}selected="selected"{/if}>All Existing</option>
                </select>
            </td>
        </tr>
        <tr>
            <td scope="row" width="2">
                {if $config.kashflow_api.get_products == "1"}
                {assign var="checked" value='checked="checked"'}
                {else}
                {assign var="checked" value=""}
                {/if}
                <input type="hidden" value ='0' name="kashflow_api[get_products]">
                <input type="checkbox" class="kashflow_api" id="kashflow_api[get_products]" name="kashflow_api[get_products]" value="1" {$checked} >
                <span> {$MOD.LBL_KASHFLOW_GET_PRODUCTS}</span>
            </td>
        </tr>
        <tr>
            <td scope="row" width="2">
                {if $config.kashflow_api.send_invoices == "1"}
                {assign var="checked" value='checked="checked"'}
                {else}
                {assign var="checked" value=""}
                {/if}
                <input type="hidden" value ='0' name="kashflow_api[send_invoices]">
                <input type="checkbox" class="kashflow_api" id="kashflow_api[send_invoices]" name="kashflow_api[send_invoices]" value="1" {$checked} >
                <span> {$MOD.LBL_KASHFLOW_SEND_INVOICES}</span>
            </td>
            <td scope="row" width="2">
                <select class="kashflow_api" id="kashflow_api[send_invoices_option]" name="kashflow_api[send_invoices_option]">
                    <option label="New Records" value="new" {if $config.kashflow_api.send_invoices_option == "new"}selected="selected"{/if}>New Records</option>
                    <option label="Modified Records" value="modified" {if $config.kashflow_api.send_invoices_option == "modified"}selected="selected"{/if}>Modified Records</option>
                    <option label="All Existing" value="all" {if $config.kashflow_api.send_invoices_option == "all"}selected="selected"{/if}>All Existing</option>
                </select>
            </td>
        </tr>
        <tr>
            <td scope="row" width="2">
                {if $config.kashflow_api.get_invoices == "1"}
                {assign var="checked" value='checked="checked"'}
                {else}
                {assign var="checked" value=""}
                {/if}
                <input type="hidden" value ='0' name="kashflow_api[get_invoices]">
                <input type="checkbox" class="kashflow_api" id="kashflow_api[get_invoices]" name="kashflow_api[get_invoices]" value="1" {$checked} >
                <span> {$MOD.LBL_KASHFLOW_GET_INVOICES}</span>
            </td>
        </tr>
        <tr>
            <td scope="row" width="2">
                {if $config.kashflow_api.get_invoice_pdf == "1"}
                {assign var="checked" value='checked="checked"'}
                {else}
                {assign var="checked" value=""}
                {/if}
                <input type="hidden" value ='0' name="kashflow_api[get_invoice_pdf]">
                <input type="checkbox" class="kashflow_api" id="kashflow_api[get_invoice_pdf]" name="kashflow_api[get_invoice_pdf]" value="1" {$checked} >
                <span> {$MOD.LBL_KASHFLOW_GET_INVOICE_PDF}</span>
            </td>
        </tr>
    </table>

    <div style="padding-top: 2px;">
        {$BUTTONS}
    </div>
    {$JAVASCRIPT}

    <div id="kashflowTestConnectionModal" class="modal bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">&nbsp;</h4>
                </div>
                <div style="height:85px;" class="modal-body">
                    <p style='text-align:center;' class="message">{$MOD.LBL_KASHFLOW_TEST_CONNECTION_LOADING}</p>
                    <p style='text-align:center;' class="loadingGif">{$LOADING_GIF}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="kashflowTestConnectionClose" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="kashflowGetNominalCodesModal" class="modal bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">&nbsp;</h4>
                </div>
                <div style="height:85px;" class="modal-body">
                    <p style='text-align:center;' class="message">{$MOD.LBL_KASHFLOW_GET_NOMINAL_CODES_LOADING}</p>
                    <p style='text-align:center;' class="loadingGif">{$LOADING_GIF}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="kashflowGetNominalCodesClose" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="custom/modules/Administration/KashflowTestConnection.js"></script>
</form>
