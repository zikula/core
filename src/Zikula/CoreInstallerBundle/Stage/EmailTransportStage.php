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

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\MailerModule\Form\Type\MailTransportConfigType;
use Zikula\MailerModule\Helper\MailTransportHelper;

class EmailTransportStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return 'emailtransport';
    }

    public function getFormType(): string
    {
        return MailTransportConfigType::class;
    }

    public function getFormOptions(): array
    {
        return [];
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Install/mailer.html.twig';
    }

    public function isNecessary(): bool
    {
        $mailerDsn = $_ENV['MAILER_DSN'] ?? '';
        if (!empty($mailerDsn) && 'smtp://localhost' !== $mailerDsn) {
            return false;
        }

        return true;
    }

    public function getTemplateParams(): array
    {
        return [];
    }

    public function handleFormResult(FormInterface $form): bool
    {
        $projectDir = $this->container->getParameter('kernel.project_dir');

        return (new MailTransportHelper($projectDir))->handleFormData($form->getData());
    }
}
