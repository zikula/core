<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Bundle\CoreInstallerBundle\Form\AbstractType;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\Wizard;
use Zikula\Component\Wizard\WizardCompleteInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Response\PlainResponse;

/**
 * Class UpgraderController
 */
class UpgraderController extends AbstractController
{
    const ZIKULACORE_MINIMUM_UPGRADE_VERSION = '1.3.6';

    /**
     * @param Request $request
     * @param string $stage
     * @return Response
     */
    public function upgradeAction(Request $request, $stage)
    {
        $currentVersion = $this->container->getParameter(\Zikula_Core::CORE_INSTALLED_VERSION_PARAM);
        if (version_compare($currentVersion, \Zikula_Core::VERSION_NUM, '=')) {
            $stage = 'complete';
        }
        // notinstalled?
        if (($this->container->getParameter('installed') == false)) {
            return new RedirectResponse($this->router->generate('install', [], RouterInterface::ABSOLUTE_URL));
        }

        // check php
        $ini_warnings = $this->util->initPhp();
        if (count($ini_warnings) > 0) {
            $request->getSession()->getFlashBag()->add('warning', implode('<hr>', $ini_warnings));
        }

        $this->container->setParameter('upgrading', true);

        // begin the wizard
        $wizard = new Wizard($this->container, realpath(__DIR__ . '/../Resources/config/upgrade_stages.yml'));
        $currentStage = $wizard->getCurrentStage($stage);
        if ($currentStage instanceof WizardCompleteInterface) {
            return $currentStage->getResponse($request);
        }
        $templateParams = $this->util->getTemplateGlobals($currentStage);
        $templateParams['headertemplate'] = '@ZikulaCoreInstaller/upgradeheader.html.twig';
        if ($wizard->isHalted()) {
            $request->getSession()->getFlashBag()->add('danger', $wizard->getWarning());

            return $this->templatingService->renderResponse('ZikulaCoreInstallerBundle::error.html.twig', $templateParams, new PlainResponse());
        }

        // handle the form
        if ($currentStage instanceof FormHandlerInterface) {
            $form = $this->form->create($currentStage->getFormType(), null, $currentStage->getFormOptions());
            if ($form instanceof AbstractType) {
                $form->setTranslator($this->translator);
            }
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $currentStage->handleFormResult($form);
                $url = $this->router->generate('upgrade', ['stage' => $wizard->getNextStage()->getName()], RouterInterface::ABSOLUTE_URL);

                return new RedirectResponse($url);
            }
            $templateParams['form'] = $form->createView();
        }

        return $this->templatingService->renderResponse($currentStage->getTemplateName(), $templateParams, new PlainResponse());
    }
}
