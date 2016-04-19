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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use Zikula\Component\Wizard\InjectContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Component\Wizard\WizardCompleteInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Swift_Mailer;
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
        $admin = \UserUtil::getVars(2);
        if ($this->sendEmailToAdmin($request, $admin)) {
            $request->getSession()->getFlashBag()->add('success', __('Congratulations! Zikula has been successfully installed.'));

            return new RedirectResponse($this->container->get('router')->generate('zikulaadminmodule_admin_adminpanel', array(), RouterInterface::ABSOLUTE_URL));
        } else {
            $request->getSession()->getFlashBag()->add('warning', __('Email settings are not yet configured. Please configure them below.'));

            return new RedirectResponse($this->container->get('router')->generate('zikulamailermodule_admin_config', array(), RouterInterface::ABSOLUTE_URL));
        }
    }

    private function sendEmailToAdmin(Request $request, $admin)
    {
        $url = $request->getSchemeAndHttpHost() . $request->getBasePath();

        $body = <<<EOF
<html>
<head></head>
<body>
<h1>Hi $admin[uname]!</h1>
<p>Zikula has been successfully installed at <a href="$url">$url</a>. If you have further questions,
visit <a href="http://zikula.org">zikula.org</a></p>
</body>
EOF;
        $message = \Swift_Message::newInstance()
            ->setSubject(__('Zikula installation completed!'))
            ->setFrom(\System::getVar('adminmail'))
            ->setTo($admin['email'])
            ->setBody($body)
            ->setContentType('text/html')
        ;
        /**
         * @var Swift_Mailer
         */
        $mailer = $this->container->get('mailer');

        return $mailer->send($message);
    }
}
