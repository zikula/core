<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Zikula\Component\Wizard\InjectContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Component\Wizard\WizardCompleteInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class CompleteStage implements StageInterface, WizardCompleteInterface, InjectContainerInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'complete';
    }

    public function getTemplateName()
    {
        return '';
    }

    public function isNecessary()
    {
        return true;
    }

    public function getTemplateParams()
    {
        return array();
    }

    public function getResponse(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', __('Congratulations! Upgrade Complete.'));

        return new RedirectResponse($this->container->get('router')->generate('zikulaadminmodule_admin_adminpanel', array(), RouterInterface::ABSOLUTE_URL));
    }
}
