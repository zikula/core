<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Component\Wizard\WizardCompleteInterface;
use Zikula\UsersModule\Constant as UserConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class CompleteStage implements StageInterface, WizardCompleteInterface
{
    use TranslatorTrait;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        MailerInterface $mailer
    ) {
        $this->setTranslator($translator);
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    public function getName(): string
    {
        return 'complete';
    }

    public function getTemplateName(): string
    {
        return '';
    }

    public function isNecessary(): bool
    {
        return true;
    }

    public function getTemplateParams(): array
    {
        return [];
    }

    public function getResponse(Request $request): Response
    {
        if ($this->sendEmailToAdmin($request)) {
            if ($request->hasSession() && ($session = $request->getSession())) {
                $session->getFlashBag()->add('success', 'Congratulations! Zikula has been successfully installed.');
                $session->getFlashBag()->add('info', $this->trans(
                    'Session are currently configured to use the filesystem. It is recommended that you change this to use the database. Click %here% to configure.',
                    ['%here%' => '<a href="' . $this->router->generate('zikulasecuritycentermodule_config_config') . '">' . $this->trans('Security Center') . '</a>']
                ));
            }

            return new RedirectResponse($this->router->generate('zikulaadminmodule_admin_adminpanel', [], RouterInterface::ABSOLUTE_URL));
        }
        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->getFlashBag()->add('warning', $this->trans('Email settings are not yet configured or incorrectly configured. Please configure them below.'));
        }

        return new RedirectResponse($this->router->generate('zikulamailermodule_config_config', [], RouterInterface::ABSOLUTE_URL));
    }

    private function sendEmailToAdmin(Request $request): bool
    {
        $adminUser = $this->userRepository->find(UserConstant::USER_ID_ADMIN);
        $uName = $adminUser->getUname();
        $url = $request->getSchemeAndHttpHost() . $request->getBasePath();
        $locale = $request->getLocale();

        $subject = $this->trans('Zikula installation completed!');
        $body = <<<EOF
<html lang="${locale}">
<head>
    <title>${subject}</title>
</head>
<body>
<h1>Hi ${uName}!</h1>
<p>Zikula has been successfully installed at <a href="${url}">${url}</a>. If you have further questions,
visit <a href="https://ziku.la">ziku.la</a></p>
</body>
EOF;
        $email = (new Email())
            ->from($adminUser->getEmail())
            ->to($adminUser->getEmail())
            ->subject($subject)
            ->html($body);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            return false;
        }

        return true;
    }
}
