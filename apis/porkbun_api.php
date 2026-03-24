<?php

/**
 * Porkbun API
 *
 * @package blesta
 * @subpackage blesta.components.modules.porkbun.apis
 * @copyright Copyright (c) 2025, BiswasHost
 * @link https://www.biswashost.com
 */
class PorkbunApi
{
    /**
     * @var string API Key
     */
    private $apikey;

    /**
     * @var string Secret API Key
     */
    private $secretapikey;

    /**
     * @var string API base URL
     */
    private $apiUrl = 'https://api.porkbun.com/api/json/v3';

    /**
     * @var array Last request data
     */
    private $last_request = [];

    /**
     * @var int Last HTTP response code
     */
    public $httpcode;

    /**
     * Constructor
     *
     * @param string $apikey The API key
     * @param string $secretapikey The secret API key
     */
    public function __construct($apikey, $secretapikey)
    {
        $this->apikey = $apikey;
        $this->secretapikey = $secretapikey;
    }

    /**
     * Submit an API request
     *
     * @param string $endpoint The API endpoint path (e.g., 'ping', 'domain/listAll')
     * @param array $params Additional parameters to send
     * @return PorkbunResponse
     */
    public function submit($endpoint, array $params = [])
    {
        // Sanitize endpoint: strip any path traversal attempts
        $endpoint = str_replace(['..', "\0"], '', ltrim($endpoint, '/'));
        $url = $this->apiUrl . '/' . $endpoint;

        // Always include authentication
        $params['apikey'] = $this->apikey;
        $params['secretapikey'] = $this->secretapikey;

        // Save last request (mask credentials)
        $this->last_request = [
            'url' => $url,
            'args' => array_merge($params, [
                'apikey' => str_repeat('*', 8),
                'secretapikey' => str_repeat('*', 8)
            ])
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Loader::load(dirname(__FILE__) . DS . 'porkbun_response.php');

        return new PorkbunResponse($response);
    }

    /**
     * Return last request info
     *
     * @return array
     */
    public function lastRequest()
    {
        return $this->last_request;
    }
}
