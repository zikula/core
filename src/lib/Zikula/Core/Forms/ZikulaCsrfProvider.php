<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Forms;

use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Csrf provider based on zikula csrf system.
 */
class ZikulaCsrfProvider implements CsrfTokenManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateCsrfToken($intention)
    {
        return \SecurityUtil::generateCsrfToken();
    }

    /**
     * {@inheritdoc}
     */
    public function isCsrfTokenValid($intention, $token)
    {
        return \SecurityUtil::validateCsrfToken($token);
    }
}
