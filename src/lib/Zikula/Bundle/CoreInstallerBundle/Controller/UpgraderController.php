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
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\Wizard;
use Zikula\Component\Wizard\WizardCompleteInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
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
        $currentVersion = $this->container->getParameter(ZikulaKernel::CORE_INSTALLED_VERSION_PARAM);
        if (version_compare($currentVersion, ZikulaKernel::VERSION, '=')) {
            $stage = 'complete';
        }
        // notinstalled?
        if (($this->container->getParameter('installed') == false)) {
            return new RedirectResponse($this->router->generate('install'));
        }

        // check php
        $ini_warnings = $this->controllerHelper->initPhp();
        if (count($ini_warnings) > 0) {
            $request->getSession()->getFlashBag()->add('warning', implode('<hr>', $ini_warnings));
        }

        $this->container->setParameter('upgrading', true);
        $request->setLocale($this->container->getParameter('locale'));

        // begin the wizard
        $wizard = new Wizard($this->container, realpath(__DIR__ . '/../Resources/config/upgrade_stages.yml'));
        $currentStage = $wizard->getCurrentStage($stage);
        if ($currentStage instanceof WizardCompleteInterface) {
            return $currentStage->getResponse($request);
        }
        $templateParams = $this->controllerHelper->getTemplateGlobals($currentStage);
        $templateParams['headertemplate'] = '@ZikulaCoreInstaller/upgradeheader.html.twig';
        if ($wizard->isHalted()) {
            $request->getSession()->getFlashBag()->add('danger', $wizard->getWarning());

            return $this->templatingService->renderResponse('ZikulaCoreInstallerBundle::error.html.twig', $templateParams, new PlainResponse());
        }

        // handle the form
        if ($currentStage instanceof FormHandlerInterface) {
            $form = $this->form->create($currentStage->getFormType(), null, $currentStage->getFormOptions());
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $currentStage->handleFormResult($form);
                $params = ['stage' => $wizard->getNextStage()->getName(), '_locale' => $this->container->getParameter('locale')];
                $url = $this->router->generate('upgrade', $params);

                return new RedirectResponse($url);
            }
            $templateParams['form'] = $form->createView();
        }

        return $this->templatingService->renderResponse($currentStage->getTemplateName(), $templateParams, new PlainResponse());
    }
}
