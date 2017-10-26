<?php
/**
 * Created by PhpStorm.
 * User: michael
 * Date: 25/09/17
 * Time: 10:35
 */

class Kashflow
{
    /**
     * @var null|SoapClient
     */
    private $m_client   = NULL;

    /**
     * @var string
     */
    private $m_username = "";

    /**
     * @var string
     */
    private $m_password = "";

    /**
     * Kashflow constructor.
     */
    public function __construct($connectionDetails = [])
    {
        global $sugar_config;

        if (empty($connectionDetails) && !array_key_exists('kashflow_api', $sugar_config)) {
            throw new ErrorException('No login details were found for Kashflow.');
        }

        if (!empty($connectionDetails)) {
            $this->m_username = $connectionDetails['username'];
            $this->m_password = $connectionDetails['password'];
        } else {
            $this->m_username = $sugar_config['kashflow_api']['username'];
            $this->m_password = $sugar_config['kashflow_api']['password'];
        }

        $this->m_client = new SoapClient("https://securedwebapp.com/api/service.asmx?WSDL");
    }

    /**
     * @param $fn
     * @param null $extra
     *
     * @return mixed
     */
    private function makeRequest($fn, $extra = NULL)
    {
        $parameters['UserName'] = $this->m_username;
        $parameters['Password'] = $this->m_password;
        if($extra != NULL)
            $parameters = array_merge($parameters,$extra);
        return self::handleResponse($this->m_client->$fn($parameters));
    }

    /**
     * @param $response
     *
     * @return mixed
     */
    private static function handleResponse($response)
    {
        return $response;
    }

    /**
     * @return mixed
     */
    public function insertInvoice($parameters)
    {
        return $this->makeRequest("InsertInvoice", $parameters);
    }

    public function updateInvoice($parameters)
    {
        return $this->makeRequest("UpdateInvoice", $parameters);
    }

    public function insertInvoiceLine($parameters)
    {
        return $this->makeRequest("InsertInvoiceLineWithInvoiceNumber", $parameters);
    }

    public function deleteInvoiceLine($parameters)
    {
        return $this->makeRequest("DeleteInvoiceLine", $parameters);
    }

    /**
     * @return mixed
     */
    public function getInvoice($number)
    {
        $parameters['InvoiceNumber'] = $number;
        return $this->makeRequest("GetInvoice", $parameters);
    }

    /**
     * @return mixed
     */
    public function getInvoicesByDateRange()
    {
        $parameters['StartDate'] = "2000-01-01T00:00:00";
        $parameters['EndDate'] = date('Y-m-d')."T00:00:00";
        return $this->makeRequest("GetInvoicesByDateRange", $parameters);
    }

    public function getCustomerByID($parameters)
    {
        return $this->makeRequest("GetCustomerByID", $parameters);
    }

    public function getCustomers()
    {
        return $this->makeRequest("GetCustomers");
    }

    public function insertCustomer($parameters)
    {
        return $this->makeRequest("InsertCustomer", $parameters);
    }

    public function updateCustomer($parameters)
    {
        return $this->makeRequest("UpdateCustomer", $parameters);
    }

    /**
     * @return mixed
     */
    public function addOrUpdateSubProduct($parameters = "")
    {
        return $this->makeRequest("AddOrUpdateSubProduct", $parameters);
    }

    /**
     * @return mixed
     */
    public function getSubProducts($code)
    {
        $parameters = "";
        $parameters['NominalID'] = $code;
        return $this->makeRequest("GetSubProducts", $parameters);
    }

    /**
     * @return string
     */
    public function checkLoginDetails() {
        return $this->makeRequest('GetCustomers');
    }

    /**
     * @return string
     */
    public function getNominalCodes() {
        return $this->makeRequest('GetNominalCodes');
    }
}