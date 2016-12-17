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
 * Abstract AJAX controller.
 * @deprecated
 */
abstract class Zikula_Controller_AbstractAjax extends Zikula_AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected function configureView()
    {
        // View is generally not required so override this.
    }

    /**
     * Check the CSRF token.
     *
     * Checks will fall back to $token check if automatic checking fails.
     *
     * @param string $token Token, default null
     *
     * @throws AccessDeniedException If the CSFR token fails
     *
     * @return void
     */
    public function checkAjaxToken($token = null)
    {
        @trigger_error('Old controller is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        $headerToken = isset($_SERVER['HTTP_X_ZIKULA_AJAX_TOKEN']) ? $_SERVER['HTTP_X_ZIKULA_AJAX_TOKEN'] : null;

        if ($headerToken == session_id()) {
            return;
        }

        try {
            $this->checkCsrfToken($token);
        } catch (AccessDeniedException $e) {
        }

        throw new AccessDeniedException(__('Ajax security checks failed.'));
    }
}
