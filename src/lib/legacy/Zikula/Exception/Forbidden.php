<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Zikula_Exception_Forbidden class.
 *
 * @deprecated since 1.4.0
 * @see AccessDeniedException
 */
class Zikula_Exception_Forbidden extends AccessDeniedException
{
}
