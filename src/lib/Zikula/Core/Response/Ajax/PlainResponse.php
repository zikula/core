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

namespace Zikula\Core\Response\Ajax;

/**
 * Ajax class.
 */
class PlainResponse extends AbstractBaseResponse
{
    /**
     * Constructor.
     *
     * @param string $payload Payload data.
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
        parent::__construct('', $this->statusCode);
    }

    /**
     * Convert class to string.
     *
     * @return string
     */
    public function __toString()
    {
        $this->setContent($this->payload);
        $this->headers->set('Content-type', 'text/html');

        return
            sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\r\n".
            $this->headers."\r\n".
            $this->getContent();
    }
}
