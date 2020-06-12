<?php

/**
 * Class Paylike
 *
 * @package Paylike
 */
class Paylike_Paylike
{
    /**
     * @var string
     */
    const BASE_URL = 'https://api.paylike.io';

    /**
     * @var Paylike_HttpClient_HttpClientInterface
     */
    public $client;

    /**
     * @var string
     */
    private $api_key;


    /**
     * Paylike constructor.
     *
     * @param                          $api_key
     * @param Paylike_HttpClient_HttpClientInterface $client
     * @throws Paylike_Exception_ApiException
     */
    public function __construct($api_key, Paylike_HttpClient_HttpClientInterface $client = null)
    {
        $this->api_key = $api_key;
        $this->client  = $client ? $client
            : new Paylike_HttpClient_CurlClient($this->api_key, self::BASE_URL);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }


    /**
     * @return Paylike_Endpoint_Apps
     */
    public function apps()
    {
        return new Paylike_Endpoint_Apps($this);
    }

    /**
     * @return Paylike_Endpoint_Merchants
     */
    public function merchants()
    {
        return new Paylike_Endpoint_Merchants($this);
    }

    /**
     * @return Paylike_Endpoint_Transactions
     */
    public function transactions()
    {
        return new Paylike_Endpoint_Transactions($this);
    }

    /**
     * @return Paylike_Endpoint_Cards
     */
    public function cards()
    {
        return new Paylike_Endpoint_Cards($this);
    }
}
