<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\AuthenticationMethodInterface;

interface ReEntrantAuthenticationMethodInterface extends AuthenticationMethodInterface
{
    /**
     * Return the ID of the user sent by the provider.
     * @return string
     */
    public function getId();

    /**
     * Persist the data required to map external authorization to a Zikula UID.
     * @param $data
     */
    public function persistMapping($data);
}
