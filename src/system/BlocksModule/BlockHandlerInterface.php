<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule;

interface BlockHandlerInterface
{
    /**
     * Get the type of the block handler (e.g. the 'name').
     * This is displayed to the admin during creation, not to site users.
     */
    public function getType(): string;

    /**
     * Display the block content.
     */
    public function display(array $properties): string;

    /**
     * Get the Fully Qualified Class Name of the block's form class.
     */
    public function getFormClassName(): string;

    /**
     * Get an array of form options.
     */
    public function getFormOptions(): array;

    /**
     * Get the full name of the form's template in 'namespaced' name-style.
     *   e.g. `return '@AcmeMyBundle/Block/foo_modify.html.twig';`
     */
    public function getFormTemplate(): string;

    /**
     * Get an array of default values for custom properties.
     */
    public function getPropertyDefaults(): array;
}
