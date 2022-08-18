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

use Symfony\Component\Form\FormInterface;

interface FormHandlerInterface
{
    /**
     * Returns the FQCN of a Symfony Form Type
     */
    public function getFormType(): string;

    /**
     * Handle results of previously validated form
     */
    public function handleFormResult(FormInterface $form): bool;

    /**
     * Returns an array of options applied to the Form.
     */
    public function getFormOptions(): array;
}
