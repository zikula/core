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

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\Wizard;
use Zikula\Component\Wizard\WizardCompleteInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class UpgraderController
 * @package Zikula\Bundle\CoreInstallerBundle\Controller
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
        if (version_compare(ZIKULACORE_CURRENT_INSTALLED_VERSION, \Zikula_Core::VERSION_NUM, '=')) {
            $stage = 'complete';
        }
        // notinstalled?
        if (($this->container->getParameter('installed') == false)) {
            return new RedirectResponse($this->router->generate('install', array(), RouterInterface::ABSOLUTE_URL));
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

            return $this->templatingService->renderResponse('ZikulaCoreInstallerBundle::error.html.twig', $templateParams);
        }

        // handle the form
        if ($currentStage instanceof FormHandlerInterface) {
            $form = $this->form->create($currentStage->getFormType(), null, $currentStage->getFormOptions());
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $currentStage->handleFormResult($form);
                $url = $this->router->generate('upgrade', array('stage' => $wizard->getNextStage()->getName()), RouterInterface::ABSOLUTE_URL);

                return new RedirectResponse($url);
            }
            $templateParams['form'] = $form->createView();
        }

        return $this->templatingService->renderResponse($currentStage->getTemplateName(), $templateParams);
    }
}
