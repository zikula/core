<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Twig\Extension;

class UserAdministrationExtension extends \Twig_Extension
{
    private $administrativeActionsService;

    /**
     * UserAdministrationExtension constructor.
     * @param $administrativeActionsService
     */
    public function __construct($administrativeActionsService)
    {
        $this->administrativeActionsService = $administrativeActionsService;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulausersmodule_useradministration';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('userAdministrationActions', [$this->administrativeActionsService, 'display'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters()
    {
        return [];
    }
}
