<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Response
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Ajax class.
 */
abstract class Zikula_Response_Ajax_Base
{
    /**
     * Response code.
     *
     * @var integer
     */
    protected $responseCode = 200;

    /**
     * Payload data (not encoded).
     * 
     * @var array
     */
    protected $payload;

    /**
     * CSRF Token.
     * 
     * @var string
     */
    protected $csrfToken;

    /**
     * Flag to create a new nonce.
     * 
     * @var boolean
     */
    protected $newCsrfToken = true;

    /**
     * Reponse data.
     *
     * @var array
     */
    protected $data;

    /**
     * Options array.
     * 
     * @var array
     */
    protected $options;
    
    /**
     * Constructor.
     *
     * @param mixed $data    Application data.
     * @param array $options Options.
     */
    public function __construct($data, array $options = array())
    {
        $this->data = $data;
        $this->options = $options;
        if ($this->newCsrfToken) {
            $this->csrfToken = SecurityUtil::generateAuthKey(ModUtil::getName());
        }
    }

    /**
     * Convert class to string.
     *
     * @return void
     */
    public function __toString()
    {
        $payload = json_encode($this->generatePayload());
        header($this->createHttpResponseHeader());
        header('Content-type: application/json');
        echo $payload;
    }

    /**
     * Generates payload.
     *
     * @return array
     */
    protected function generatePayload()
    {
        return array(
                'core' => $this->generateCoreData(),
                'data' => $this->data,
        );
    }

    /**
     * Generate system level payload.
     * 
     * @return array
     */
    protected function generateCoreData()
    {
        $core = array();

        if ($this->options) {
            foreach ($this->options as $key => $value) {
                $core[$key] = $value;
            }
        }

        if ($this->csrfToken) {
            $core['authid'] = $this->csrfToken;
        }
        $core['statusmsg'] = LogUtil::getStatusMessages();
        
        return $core;
    }

    /**
     * Create Http Response Header.
     *
     * @return string
     */
    protected function createHttpResponseHeader()
    {
        switch ($this->responseCode) {
            case '200':
                $response = '200 OK';
                break;
            case '400':
                $response = '400 Bad data';
                break;
            case '403':
                $response = '403 Forbidden';
                break;
            case '404':
                $response = '404 Not found';
                break;
            case '503':
                $response = '503 Temporarily unavailable';
                break;
            case '500':
                $response = '500 Fatal error';
                break;
            default:
                $response = '500 Fatal error';
                break;
        }

        return "HTTP/1.1 $response";
    }

    /**
     * Add options.
     *
     * @param string $key   Option key.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function addOptions($key, $value)
    {
        $this->options[$key] = $value;
    }

}
