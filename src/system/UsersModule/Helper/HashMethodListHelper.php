<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

/**
 * Defines the valid list of password hashing methods.
 */
class HashMethodListHelper
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
            'sha256' => 'sha256',
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
