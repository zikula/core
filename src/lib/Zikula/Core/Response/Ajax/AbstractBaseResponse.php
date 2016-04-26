<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Response\Ajax;

use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Response\PlainResponse;

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
     * @var mixed
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
        return json_encode([
            'core' => $this->generateCoreData(),
            'data' => $this->payload,
        ]);
    }

    /**
     * Generate system level payload.
     *
     * @return array
     */
    protected function generateCoreData()
    {
        $core = [];

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
