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

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\Wizard;
use Zikula\Component\Wizard\WizardCompleteInterface;

/**
 * Class UpgraderController
 */
class UpgraderController extends AbstractController
{
    public const ZIKULACORE_MINIMUM_UPGRADE_VERSION = '1.4.3';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->router = $container->get('router');
        $this->form = $this->container->get('form.factory');
    }

    public function upgradeAction(Request $request, $stage): Response
    {
        $currentVersion = $this->container->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
        if (version_compare($currentVersion, ZikulaKernel::VERSION, '=')) {
            $stage = 'complete';
        }
        // not installed?
        if (false === $this->container->getParameter('installed')) {
            return new RedirectResponse($this->router->generate('install'));
        }

        $session = $request->hasSession() ? $request->getSession() : null;

        // check php
        $ini_warnings = $this->controllerHelper->initPhp();
        if (null !== $session && 0 < count($ini_warnings)) {
            $session->getFlashBag()->add('warning', implode('<hr />', $ini_warnings));
        }

        $yamlDumper = new YamlDumper($this->container->get('kernel')->getProjectDir() . '/config', 'services_custom.yaml');
        $yamlDumper->setParameter('upgrading', true);
        $request->setLocale($this->container->getParameter('locale'));

        // begin the wizard
        $wizard = new Wizard($this->container, dirname(__DIR__) . '/Resources/config/upgrade_stages.yaml');
        $currentStage = $wizard->getCurrentStage($stage);
        if ($currentStage instanceof WizardCompleteInterface) {
            $yamlDumper->setParameter('upgrading', false);

            return $currentStage->getResponse($request);
        }
        $templateParams = $this->controllerHelper->getTemplateGlobals($currentStage);
        $templateParams['headertemplate'] = '@ZikulaCoreInstaller/upgradeheader.html.twig';
        if ($wizard->isHalted()) {
            if (null !== $session) {
                $session->getFlashBag()->add('danger', $wizard->getWarning());
            }

            return $this->renderResponse('@ZikulaCoreInstaller/error.html.twig', $templateParams);
        }

        // handle the form
        if ($currentStage instanceof FormHandlerInterface) {
            $form = $this->form->create($currentStage->getFormType(), null, $currentStage->getFormOptions());
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $currentStage->handleFormResult($form);
                $params = [
                    'stage' => $wizard->getNextStage()->getName(),
                    '_locale' => $this->container->getParameter('locale')
                ];
                $url = $this->router->generate('upgrade', $params);

                return new RedirectResponse($url);
            }
            $templateParams['form'] = $form->createView();
        }

        return $this->renderResponse($currentStage->getTemplateName(), $templateParams);
    }
}
