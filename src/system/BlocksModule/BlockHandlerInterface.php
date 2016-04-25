<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
     * @return string
     */
    public function getType();

    /**
     * Display the block content.
     * @param array $properties
     * @return string
     */
    public function display(array $properties);

    /**
     * Get the Fully Qualified Class Name of the block's form class.
     * @return string
     */
    public function getFormClassName();

    /**
     * Get an array of form options.
     * @return array
     */
    public function getFormOptions();

    /**
     * Get the full name of the form's template in 'namespaced' name-style.
     *   e.g. `return '@AcmeMyBundle/Block/foo_modify.html.twig';`
     * @return string
     */
    public function getFormTemplate();
}
