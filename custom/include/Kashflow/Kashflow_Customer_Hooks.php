<?php
require_once 'custom/include/Kashflow/Kashflow.php';
require_once 'modules/Contacts/Contact.php';
class Kashflow_Customer_Hooks {

    /**
     * @param $bean
     * @param $event
     * @param $arguments
     */
    function addOrUpdateCustomer($bean, $event, $arguments)
    {
        global $sugar_config;
        if ($sugar_config['kashflow_api']['send_invoices'] == 1 && $bean->from_kashflow == false &&
            (($sugar_config['kashflow_api']['send_invoices_option'] == 'modified' && $bean->date_entered != $bean->date_modified) ||
             ($sugar_config['kashflow_api']['send_invoices_option'] == 'new' && $bean->date_entered == $bean->date_modified) ||
              $sugar_config['kashflow_api']['send_invoices_option'] == 'all')) {

            $kashflow = new Kashflow();

            if($bean->module_dir == "Accounts"){
                $this->sendCustomerDetails($bean, $kashflow);
            }
            elseif($bean->module_dir == "Contacts" && !empty($bean->account_id) && $bean->billing_contact == true) {
                $accountBean = BeanFactory::getBean("Accounts", $bean->account_id);
                $this->sendCustomerDetails($accountBean, $kashflow, $bean);
            }
        }
    }

    function sendCustomerDetails($account, $kashflow, $contact = "") {

        global $app_strings;
        if(!empty($account->kashflow_id)) {
            $customerCode['CustomerCode'] = $account->kashflow_id;
            $customer = $kashflow->getCustomerByID($customerCode);
            $parameters['custr'] = $customer->GetCustomerByIDResult;
        }

        $parameters['custr']->CustomerID = !empty($account->kashflow_id) ? $account->kashflow_id : 0;
        $parameters['custr']->Code = $account->kashflow_code;
        $parameters['custr']->Name = $account->name;
        if(!empty($contact)) {
            $parameters['custr']->ContactTitle = !empty($contact->salutation) ? $contact->salutation : "";
            $parameters['custr']->ContactFirstName = !empty($contact->first_name) ? $contact->first_name : "";
            $parameters['custr']->ContactLastName = !empty($contact->last_name) ? $contact->last_name : "";
            $parameters['custr']->Mobile = !empty($contact->mobile_phone) ? $contact->mobile_phone : "";
        }
        $parameters['custr']->Telephone = !empty($account->phone_office) ? $account->phone_office : "";
        $parameters['custr']->Fax = !empty($account->fax) ? $account->fax : "";
        $parameters['custr']->Email = $account->email;
        $parameters['custr']->Address1 = $account->billing_address_street;
        $parameters['custr']->Address2 = $account->billing_address_city;
        $parameters['custr']->Address3 = $account->billing_address_state;
        $parameters['custr']->Address4 = $account->billing_address_country;
        $parameters['custr']->Postcode = !empty($account->billing_address_postcode) ? $account->billing_address_postcode : "";
        $parameters['custr']->Website = $account->website;

        if(empty($account->kashflow_id)) {
            $parameters = $this->addDefaultEntries($parameters, $account);
            $response = $kashflow->insertCustomer($parameters);
            if(!empty($response->InsertCustomerResult)) $account->kashflow_id = $response->InsertCustomerResult;
            if($response->Status == "NO") SugarApplication::appendErrorMessage($app_strings['LBL_FAILED_KASHFLOW_CUSTOMER']);
        } else {
            $response = $kashflow->updateCustomer($parameters);
            if($response->Status == "NO") SugarApplication::appendErrorMessage($app_strings['LBL_FAILED_KASHFLOW_CUSTOMER']);
        }
    }

    function addDefaultEntries($parameters, $bean) {
        $parameters['custr']->Created = date('Y-m-d')."T00:00:00";
        $parameters['custr']->Updated = date('Y-m-d')."T00:00:00";
        $parameters['custr']->EC = 0;
        $parameters['custr']->OutsideEC = 0;
        $parameters['custr']->Notes = "";
        $parameters['custr']->Source = 0;
        $parameters['custr']->Discount = "0.0000";
        $parameters['custr']->ShowDiscount = false;
        $parameters['custr']->PaymentTerms = 28;
        $parameters['custr']->ExtraText1 = "";
        $parameters['custr']->ExtraText2 = "";
        $parameters['custr']->ExtraText3 = "";
        $parameters['custr']->ExtraText4 = "";
        $parameters['custr']->ExtraText5 = "";
        $parameters['custr']->ExtraText6 = "";
        $parameters['custr']->ExtraText7 = "";
        $parameters['custr']->ExtraText8 = "";
        $parameters['custr']->ExtraText9 = "";
        $parameters['custr']->ExtraText10 = "";
        $parameters['custr']->ExtraText11 = "";
        $parameters['custr']->ExtraText12 = "";
        $parameters['custr']->ExtraText13 = "";
        $parameters['custr']->ExtraText14 = "";
        $parameters['custr']->ExtraText15 = "";
        $parameters['custr']->ExtraText16 = "";
        $parameters['custr']->ExtraText17 = "";
        $parameters['custr']->ExtraText18 = "";
        $parameters['custr']->ExtraText19 = "";
        $parameters['custr']->ExtraText20 = "";
        $parameters['custr']->CheckBox1 = 0;
        $parameters['custr']->CheckBox2 = 0;
        $parameters['custr']->CheckBox3 = 0;
        $parameters['custr']->CheckBox4 = 0;
        $parameters['custr']->CheckBox5 = 0;
        $parameters['custr']->CheckBox6 = 0;
        $parameters['custr']->CheckBox7 = 0;
        $parameters['custr']->CheckBox8 = 0;
        $parameters['custr']->CheckBox9 = 0;
        $parameters['custr']->CheckBox10 = 0;
        $parameters['custr']->CheckBox11 = 0;
        $parameters['custr']->CheckBox12 = 0;
        $parameters['custr']->CheckBox13 = 0;
        $parameters['custr']->CheckBox14 = 0;
        $parameters['custr']->CheckBox15 = 0;
        $parameters['custr']->CheckBox16 = 0;
        $parameters['custr']->CheckBox17 = 0;
        $parameters['custr']->CheckBox18 = 0;
        $parameters['custr']->CheckBox19 = 0;
        $parameters['custr']->CheckBox20 = 0;
        $parameters['custr']->CurrencyID = 0;
        $parameters['custr']->CustHasDeliveryAddress = 0;
        $parameters['custr']->DeliveryAddress1 = "";
        $parameters['custr']->DeliveryAddress2 = "";
        $parameters['custr']->DeliveryAddress3 = "";
        $parameters['custr']->DeliveryAddress4 = "";
        $parameters['custr']->DeliveryCountryName = "United Kingdom";
        $parameters['custr']->DeliveryCountryCode = "GB";
        $parameters['custr']->DeliveryPostcode = "";
        $parameters['custr']->VATNumber = "";
        return $parameters;
    }
}