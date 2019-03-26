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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Upgrade;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\Translator;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Component\Wizard\WizardCompleteInterface;

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
        return [];
    }

    public function getResponse(Request $request)
    {
        $request->getSession()->getFlashBag()->add('success', $this->container->get(Translator::class)->__('Congratulations! Upgrade Complete.'));

        return new RedirectResponse($this->container->get('router')->generate('zikulaadminmodule_admin_adminpanel', [], RouterInterface::ABSOLUTE_URL));
    }
}
