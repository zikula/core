<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
