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
     * @throws Exception
     */
    private static function handleResponse($response)
    {
        if($response->Status == "NO")
            throw(new Exception($response->StatusDetail));
        return $response;
    }

    /**
     * @return mixed
     */
    public function insertInvoice($parameters)
    {
        $test = 0;
        if($test != 0) {
            $line = array
            (
                "LineID"      => 0,
                "Quantity"    => 1,
                "Description" => "description",
                "Rate"        => 9,
                "ChargeType"  => 0,
                "VatAmount"   => 0,
                "VatRate"     => 0,
                "Sort"        => 1,
                "ProductID"   => 0,
                "ProjID"      => 0,
            );

            $lines[] = new SoapVar($line,0,"InvoiceLine","KashFlow");

            $parameters['Inv'] = array
            (
                "InvoiceDBID"   => 91828459,
                "InvoiceNumber" => 3,
                "InvoiceDate"   => "2017-09-20T00:00:00",
                "DueDate"       => "2017-09-30T00:00:00",
                "CustomerID"    => 72177290,
                "Paid"          => 0,
                "SuppressTotal" => 0,
                "ProjectID"     => 0,
                "ExchangeRate"  => "0.0000",
                "Lines"         => $lines,
                "NetAmount"     => "0.0000",
                "VATAmount"     => "0.0000",
                "AmountPaid"    => "0.0000",
                "UseCustomDeliveryAddress"  => false,
            );
        }

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

    public function getCustomer($parameters)
    {
        return $this->makeRequest("GetCustomer", $parameters);
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
        $test = 0;
        if($test != 0) {
            $parameters['sp'] = array
            (
                "id"            => 0,
                "ParentID"      => 22784360,
                "Name"          => "Product Test 2",
                "Code"          => "PROD02",
                "Description"   => "test",
                "Price"         => 10,
                "VatRate"       => "0.0000",
                "WholesalePrice"=> "0.0000",
                "Managed"       => 0,
                "QtyInStock"    => 10,
                "StockWarnQty"  => 0,
                "AutoFill"      => 0
            );
        }

        return $this->makeRequest("AddOrUpdateSubProduct", $parameters);
    }

    /**
     * @return mixed
     */
    public function getSubProducts($code)
    {
        $test = 0;
        $parameters = "";
        $parameters['NominalID'] = $code;
        if($test != 0) {
            $parameters['NominalID'] = 22784360;
        }
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