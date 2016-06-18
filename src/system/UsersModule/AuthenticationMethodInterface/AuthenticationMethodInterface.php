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

interface AuthenticationMethodInterface
{
    /**
     * A displayable title.
     * @return string
     */
    public function getDisplayName();

    /**
     * Describe the nature of this method.
     * @return string
     */
    public function getDescription();

    /**
     * Authenticate the user from the provided data and return the associated native uid.
     * @param array $data
     * @return integer|null
     */
    public function authenticate(array $data);
}
