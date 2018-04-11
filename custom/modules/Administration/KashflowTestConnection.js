
$(document).ready(function(){
  // Do call to api to check login details.
  $('#kashflowTestConnection').on('click', function(e){
    e.preventDefault();
    $('#kashflowTestConnectionModal').modal('show');
    checkConnection($('input[name^="kashflow_api"]').serializeArray());
  });

  // Reset message when cancel is clicked.
  $('#kashflowTestConnectionClose').on('click', function(e){
    e.preventDefault();
    resetMessage();
  });

  function resetMessage() {
    $('#kashflowTestConnectionModal').modal('hide');
    $('.message').html(SUGAR.language.translate('Administration', 'LBL_KASHFLOW_TEST_CONNECTION_LOADING'));
    $('.loadingGif').show();
  }

  var checkConnection = function(data) {
    $.ajax({
      method: "POST",
      url: 'index.php?module=Administration&action=KashflowTestConnection&to_pdf=true',
      data: data
    }) .always(function(result) {
      if (result) {
        $('.message').html(SUGAR.language.translate('Administration', 'LBL_KASHFLOW_TEST_CONNECTION_SUCCESS'));
      } else {
        $('.message').html(SUGAR.language.translate('Administration', 'LBL_KASHFLOW_TEST_CONNECTION_FAILURE'));
      }
      $('.loadingGif').hide();
    });
  };

  $('#kashflowGetNominalCodes').on('click', function(e){
    $('#kashflowGetNominalCodesModal').modal('show');
    e.preventDefault();
    getNominalCodes($('input[name^="kashflow_api"]').serializeArray());
  });

  // Reset message when cancel is clicked.
  $('#kashflowGetNominalCodesClose').on('click', function(e){
    e.preventDefault();
    resetMessageNominalCodes();
  });

  function resetMessageNominalCodes() {
    $('#kashflowGetNominalCodesModal').modal('hide');
    $('.message').html(SUGAR.language.translate('Administration', 'LBL_KASHFLOW_GET_NOMINAL_CODES_LOADING'));
    $('.loadingGif').show();
  }

  var getNominalCodes = function(data) {
    $.ajax({
      method: "POST",
      url: 'index.php?module=Administration&action=KashflowGetNominalCodes&to_pdf=true',
      data: data
    }) .always(function(result) {
      if (result) {
        $('.message').html(SUGAR.language.translate('Administration', 'LBL_KASHFLOW_GET_NOMINAL_CODES_SUCCESS'));
      } else {
        $('.message').html(SUGAR.language.translate('Administration', 'LBL_KASHFLOW_GET_NOMINAL_CODES_FAILURE'));
      }
      $('.loadingGif').hide();
    });
  };

  $('#kashflowGetAllInvoices').on('click', function(e){
    $('#kashflowGetAllInvoicesModal').modal('show');
    e.preventDefault();
    getAllInvoices($('input[name^="kashflow_api"]').serializeArray());
  });

  // Reset message when cancel is clicked.
  $('#kashflowGetAllInvoicesClose').on('click', function(e){
    e.preventDefault();
    resetMessageInvoices();
  });

  function resetMessageInvoices() {
    $('#kashflowGetAllInvoicesModal').modal('hide');
    $('.message').html(SUGAR.language.translate('Administration', 'LBL_KASHFLOW_GET_ALL_INVOICES_LOADING'));
    $('.loadingGif').show();
  }

  var getAllInvoices = function(data) {
    $.ajax({
      method: "POST",
      url: 'index.php?module=Administration&action=KashflowGetAllInvoices&to_pdf=true',
      data: data
    }) .always(function(result) {
      if (result) {
        $('.message').html(SUGAR.language.translate('Administration', 'LBL_KASHFLOW_GET_ALL_INVOICES_SUCCESS'));
      } else {
        $('.message').html(SUGAR.language.translate('Administration', 'LBL_KASHFLOW_GET_ALL_INVOICES_FAILURE'));
      }
      $('.loadingGif').hide();
    });
  };
});


