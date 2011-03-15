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
 * Ajax base class.
 */
abstract class Zikula_Response_Ajax_AbstractBase
{
    /**
     * Response code.
     *
     * @var integer
     */
    protected $responseCode = 200;

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
}
