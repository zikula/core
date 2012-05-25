<?php

namespace Zikula\Core\Form;

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
