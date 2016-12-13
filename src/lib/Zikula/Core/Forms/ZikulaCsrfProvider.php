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
 *
 * @deprecated
 * @todo remove for Core-2.0
 */
class ZikulaCsrfProvider implements CsrfTokenManagerInterface
{
    /**
     * Obsolete method, but kept for BC.
     */
    public function generateCsrfToken($intention)
    {
        return \SecurityUtil::generateCsrfToken();
    }

    /**
     * Obsolete method, but kept for BC.
     */
    public function isCsrfTokenValid($intention, $token)
    {
        return \SecurityUtil::validateCsrfToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function getToken($tokenId)
    {
        return $this->generateCsrfToken('');
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken($tokenId)
    {
        return $this->generateCsrfToken('');
    }

    /**
     * {@inheritdoc}
     */
    public function removeToken($tokenId)
    {
        return $this->generateCsrfToken('');
    }

    /**
     * {@inheritdoc}
     */
    public function isTokenValid($token)
    {
        return $this->isCsrfTokenValid('', $token);
    }
}
