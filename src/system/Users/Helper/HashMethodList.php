<?php
/**
 * Copyright 2011 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Defines the valid list of password hashing methods.
 */
class Users_Helper_HashMethodList
{
    /**
     * Retrieve an array containing the valid hashing methods.
     *
     * @return array The valid hashing methods.
     */
    public function getHashMethods()
    {
        return array(
            'sha1'  => 'sha1',
            'sha256'=> 'sha256',
        );
    }

    /**
     * Retreive a PCRE regular expression that can be used to match a string against the valid hashing methods.
     *
     * @return string The PCRE regular expression.
     */
    public function getHashMethodsRegexp()
    {
        return '/(?:sha1|sha256)/';
    }
}
