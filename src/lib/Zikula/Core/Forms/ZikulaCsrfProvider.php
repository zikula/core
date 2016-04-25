<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Forms;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

/**
 * Csrf provider based on zikula csrf system.
 */
class ZikulaCsrfProvider implements CsrfProviderInterface
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
