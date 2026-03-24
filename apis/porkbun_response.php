<?php

/**
 * Porkbun API Response
 *
 * @package blesta
 * @subpackage blesta.components.modules.porkbun.apis
 * @copyright Copyright (c) 2025, BiswasHost
 * @link https://www.biswashost.com
 */
class PorkbunResponse
{
    /**
     * @var string The raw response from the API
     */
    private $raw;

    /**
     * @var stdClass The JSON parsed response from the API
     */
    private $json;

    /**
     * Sets the raw response
     *
     * @param string $response The raw response from the API
     */
    public function __construct($response)
    {
        $this->raw = $response;
        $this->json = json_decode($response);
    }

    /**
     * Returns the status of the API request
     *
     * @return string 'success' or 'error'
     */
    public function status()
    {
        if ($this->json && isset($this->json->status)) {
            return strtolower($this->json->status) === 'success' ? 'success' : 'error';
        }
        return 'error';
    }

    /**
     * Returns the response data
     *
     * @return stdClass|null
     */
    public function response()
    {
        return $this->json;
    }

    /**
     * Returns the raw response
     *
     * @return string The raw response
     */
    public function raw()
    {
        return $this->raw;
    }

    /**
     * Returns any errors from the response
     *
     * @return array|false An array of errors or false if no errors
     */
    public function errors()
    {
        if ($this->status() == 'error') {
            if ($this->json && isset($this->json->message)) {
                return [(string) $this->json->message];
            }
            return ['Unknown Error'];
        }
        return false;
    }
}
