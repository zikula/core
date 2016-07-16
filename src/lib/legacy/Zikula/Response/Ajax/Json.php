<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Core\Response\Ajax\AbstractBaseResponse;

/**
 * Ajax class.
 *
 * @deprecated
 */
class Zikula_Response_Ajax_Json extends AbstractBaseResponse
{
    /**
     * Constructor.
     *
     * @param string $payload Payload data
     */
    public function __construct($payload)
    {
        $this->payload = json_encode($payload);
        parent::__construct($this->payload, $this->statusCode);
        $this->headers->set('Content-type', 'application/json');
    }

    /**
     * Convert class to string.
     *
     * @return string
     */
    public function __toString()
    {
        return
            sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\r\n".
            $this->headers."\r\n".
            $this->getContent();
    }
}
