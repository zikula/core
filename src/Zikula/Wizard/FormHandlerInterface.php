<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
