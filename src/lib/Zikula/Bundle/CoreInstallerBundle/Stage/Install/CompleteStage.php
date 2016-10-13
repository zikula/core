<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        return [];
    }

    public function getResponse(Request $request)
    {
        $admin = $this->container->get('zikula_users_module.current_user');
        if ($this->sendEmailToAdmin($request, $admin)) {
            $request->getSession()->getFlashBag()->add('success', __('Congratulations! Zikula has been successfully installed.'));

            return new RedirectResponse($this->container->get('router')->generate('zikulaadminmodule_admin_adminpanel', [], RouterInterface::ABSOLUTE_URL));
        } else {
            $request->getSession()->getFlashBag()->add('warning', __('Email settings are not yet configured. Please configure them below.'));

            return new RedirectResponse($this->container->get('router')->generate('zikulamailermodule_config_config', [], RouterInterface::ABSOLUTE_URL));
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
