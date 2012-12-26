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

namespace Zikula\Framework\Response\Ajax;

use Zikula\Framework\Response\PlainResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ajax class.
 */
abstract class AbstractBaseResponse extends PlainResponse
{
    /**
     * Response code.
     *
     * @var integer
     */
    protected $statusCode = 200;

    /**
     * CSRF Token.
     *
     * @var string
     */
    protected $csrfToken;

    /**
     * Authid Token.
     *
     * @var string
     */
    protected $authid;

    /**
     * Flag to create a new nonce.
     *
     * @var boolean
     */
    protected $newCsrfToken = true;

    /**
     * The ajax payload (raw)
     *
     * @var mixed $payload
     */
    protected $payload;

    /**
     * Response status messages.
     *
     * @var array
     */
    protected $messages;

    /**
     * Options array.
     *
     * @var array
     */
    protected $options;

    /**
     * Convert class to string.
     *
     * @return string
     */
    public function __toString()
    {
        $this->setContent($this->generatePayload());
        $this->headers->set('Content-type', 'application/json');

        return parent::__toString();
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return Response
     *
     * @api
     */
    public function send()
    {
        $this->setContent($this->generatePayload());
        $this->headers->set('Content-type', 'application/json');

        return parent::send();
    }

    /**
     * Generates payload.
     *
     * @return array
     */
    protected function generatePayload()
    {
        return json_encode(array(
            'core' => $this->generateCoreData(),
            'data' => $this->payload,
        ));
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
            $core['authid'] = $this->authid;
            $core['token'] = $this->csrfToken;
        }
        //$logUtilMessages = (array) \LogUtil::getStatusMessages();
        //$core['statusmsg'] = array_merge($this->messages, $logUtilMessages);
        $core['statusmsg'] = $this->messages;

        return $core;
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
