<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LoginType;
use Zikula\Component\Wizard\StageInterface;

class LoginStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        // force upgrade of Users module before authentication check
        $usersModuleID = \ModUtil::getIdFromName('ZikulaUsersModule');
        \ModUtil::loadApi('ZikulaExtensionsModule', 'admin', true);
        \ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'upgrade', array('id' => $usersModuleID));
    }

    public function getName()
    {
        return 'login';
    }

    public function getFormType()
    {
        return new LoginType();
    }

    public function getTemplateName()
    {
        return '';
    }

    public function handleFormResult(FormInterface $form)
    {
        return;
    }

    public function isNecessary()
    {
        return true;
    }

    public function getTemplateParams()
    {
        return array();
    }
}