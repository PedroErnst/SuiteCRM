<?php
require_once 'custom/include/Kashflow/Kashflow.php';
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

            if(!empty($bean->kashflow_id) && !empty($bean->kashflow_code)) {

                $customerCode['CustomerCode'] = $bean->kashflow_code;
                $customer = $kashflow->getCustomer($customerCode);
                $parameters['custr'] = $customer->GetCustomerResult;
            }

            $bean->load_relationship('contacts');
            $contactBean = BeanFactory::getBean('Contacts', $bean->contacts->get());

            $parameters['custr']->CustomerID = !empty($bean->kashflow_id) ? $bean->kashflow_id : 0;
            $parameters['custr']->Code = $bean->kashflow_code;
            $parameters['custr']->Name = $bean->name;
            $parameters['custr']->ContactTitle = !empty($contactBean->salutation) ? $contactBean->salutation : "";
            $parameters['custr']->ContactFirstName = !empty($contactBean->first_name) ? $contactBean->first_name : "";
            $parameters['custr']->ContactLastName = !empty($contactBean->last_name) ? $contactBean->last_name : "";
            $parameters['custr']->Telephone = !empty($bean->phone_office) ? $bean->phone_office : "";
            $parameters['custr']->Mobile = !empty($contactBean->mobile_phone) ? $contactBean->mobile_phone : "";
            $parameters['custr']->Fax = !empty($bean->fax) ? $bean->fax : "";
            $parameters['custr']->Email = $bean->email;
            $parameters['custr']->Address1 = $bean->billing_address_street;
            $parameters['custr']->Address2 = $bean->billing_address_city;
            $parameters['custr']->Address3 = $bean->billing_address_state;
            $parameters['custr']->Address4 = $bean->billing_address_country;
            $parameters['custr']->Postcode = !empty($bean->billing_address_postcode) ? $bean->billing_address_postcode : "";
            $parameters['custr']->Website = $bean->website;
            $parameters['custr']->Updated = date('Y-m-d', $bean->date_modified)."T00:00:00";

            if(empty($bean->kashflow_id)) {
                $parameters['custr']['Created'] = date('Y-m-d', $bean->date_created)."T00:00:00";
                $parameters = $this->addDefaultEntries($parameters);
                $response = $kashflow->insertCustomer($parameters);
                if(!empty($response->InsertCustomerResult)) $bean->kashflow_id = $response->InsertCustomerResult;
                if($response->Status == "NO") SugarApplication::appendErrorMessage('LBL_FAILED_TO_SEND');
            } else {
                $response = $kashflow->updateCustomer($parameters);
                if($response->Status == "NO") SugarApplication::appendErrorMessage('LBL_FAILED_TO_SEND');
            }
        }
    }

    function addDefaultEntries($parameters) {
        $parameters['custr']['EC'] = 0;
        $parameters['custr']['OutsideEC'] = 0;
        $parameters['custr']['Notes'] = "";
        $parameters['custr']['Source'] = 0;
        $parameters['custr']['Discount'] = "0.0000";
        $parameters['custr']['ShowDiscount'] = false;
        $parameters['custr']['PaymentTerms'] = 28;
        $parameters['custr']['ExtraText1'] = "";
        $parameters['custr']['ExtraText2'] = "";
        $parameters['custr']['ExtraText3'] = "";
        $parameters['custr']['ExtraText4'] = "";
        $parameters['custr']['ExtraText5'] = "";
        $parameters['custr']['ExtraText6'] = "";
        $parameters['custr']['ExtraText7'] = "";
        $parameters['custr']['ExtraText8'] = "";
        $parameters['custr']['ExtraText9'] = "";
        $parameters['custr']['ExtraText10'] = "";
        $parameters['custr']['ExtraText11'] = "";
        $parameters['custr']['ExtraText12'] = "";
        $parameters['custr']['ExtraText13'] = "";
        $parameters['custr']['ExtraText14'] = "";
        $parameters['custr']['ExtraText15'] = "";
        $parameters['custr']['ExtraText16'] = "";
        $parameters['custr']['ExtraText17'] = "";
        $parameters['custr']['ExtraText18'] = "";
        $parameters['custr']['ExtraText19'] = "";
        $parameters['custr']['ExtraText20'] = "";
        $parameters['custr']['CheckBox1'] = 0;
        $parameters['custr']['CheckBox2'] = 0;
        $parameters['custr']['CheckBox3'] = 0;
        $parameters['custr']['CheckBox4'] = 0;
        $parameters['custr']['CheckBox5'] = 0;
        $parameters['custr']['CheckBox6'] = 0;
        $parameters['custr']['CheckBox7'] = 0;
        $parameters['custr']['CheckBox8'] = 0;
        $parameters['custr']['CheckBox9'] = 0;
        $parameters['custr']['CheckBox10'] = 0;
        $parameters['custr']['CheckBox11'] = 0;
        $parameters['custr']['CheckBox12'] = 0;
        $parameters['custr']['CheckBox13'] = 0;
        $parameters['custr']['CheckBox14'] = 0;
        $parameters['custr']['CheckBox15'] = 0;
        $parameters['custr']['CheckBox16'] = 0;
        $parameters['custr']['CheckBox17'] = 0;
        $parameters['custr']['CheckBox18'] = 0;
        $parameters['custr']['CheckBox19'] = 0;
        $parameters['custr']['CheckBox20'] = 0;
        $parameters['custr']['CurrencyID'] = 0;
        $parameters['custr']['CustHasDeliveryAddress'] = 0;
        $parameters['custr']['DeliveryAddress1'] = "";
        $parameters['custr']['DeliveryAddress2'] = "";
        $parameters['custr']['DeliveryAddress3'] = "";
        $parameters['custr']['DeliveryAddress4'] = "";
        $parameters['custr']['DeliveryCountryName'] = "United Kingdom";
        $parameters['custr']['DeliveryCountryCode'] = "GB";
        $parameters['custr']['DeliveryPostcode'] = "";
        $parameters['custr']['VATNumber'] = "";
        return $parameters;
    }
}