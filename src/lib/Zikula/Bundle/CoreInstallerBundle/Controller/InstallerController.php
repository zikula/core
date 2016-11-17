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
use Zikula\Core\Response\PlainResponse;

/**
 * Class InstallerController
 */
class InstallerController extends AbstractController
{
    /**
     * @param Request $request
     * @param string $stage
     * @return Response
     */
    public function installAction(Request $request, $stage)
    {
        // already installed?
        if (($stage != 'complete') && ($this->container->getParameter('installed') == true)) {
            $stage = 'installed';
        }

        // not installed but requesting installed stage?
        if (($this->container->getParameter('installed') == false) && ($stage == 'installed')) {
            $stage = 'notinstalled';
        }

        // check php
        $ini_warnings = $this->util->initPhp();
        if (count($ini_warnings) > 0) {
            $request->getSession()->getFlashBag()->add('warning', implode('<hr>', $ini_warnings));
        }

        $request->setLocale($this->container->getParameter('locale'));
        // begin the wizard
        $wizard = new Wizard($this->container, realpath(__DIR__ . '/../Resources/config/install_stages.yml'));
        $currentStage = $wizard->getCurrentStage($stage);
        if ($currentStage instanceof WizardCompleteInterface) {
            return $currentStage->getResponse($request);
        }
        $templateParams = $this->util->getTemplateGlobals($currentStage);
        $templateParams['headertemplate'] = '@ZikulaCoreInstaller/installheader.html.twig';
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
                $url = $this->router->generate('install', $params);

                return new RedirectResponse($url);
            }
            $templateParams['form'] = $form->createView();
        }

        return $this->templatingService->renderResponse($currentStage->getTemplateName(), $templateParams, new PlainResponse());
    }
}
