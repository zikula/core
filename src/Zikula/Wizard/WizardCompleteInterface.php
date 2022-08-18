<?php

declare(strict_types=1);

/**
 * Copyright Zikula.
 *
 * This work is contributed to the Zikula under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT.
 * @package Zikula
 * @author Craig Heydenburg
 *
 * Please see the LICENSE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Component\Wizard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface WizardCompleteInterface
{
    /**
     * Get the Response (probably RedirectResponse) for this completed Wizard.
     */
    public function getResponse(Request $request): Response;
}
