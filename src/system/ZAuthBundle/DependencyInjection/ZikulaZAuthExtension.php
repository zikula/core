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

namespace Zikula\ZAuthBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\ZAuthBundle\Api\UserCreationApi;
use Zikula\ZAuthBundle\AuthenticationMethod\NativeEitherAuthenticationMethod;
use Zikula\ZAuthBundle\AuthenticationMethod\NativeEmailAuthenticationMethod;
use Zikula\ZAuthBundle\AuthenticationMethod\NativeUnameAuthenticationMethod;
use Zikula\ZAuthBundle\Controller\AccountController;
use Zikula\ZAuthBundle\Controller\FileIOController;
use Zikula\ZAuthBundle\Controller\RegistrationController;
use Zikula\ZAuthBundle\Controller\UserAdministrationController;
use Zikula\ZAuthBundle\EventListener\DeletePendingRegistrationsListener;
use Zikula\ZAuthBundle\EventListener\RegistrationListener;
use Zikula\ZAuthBundle\Form\Type\AdminCreatedUserType;
use Zikula\ZAuthBundle\Form\Type\AdminModifyUserType;
use Zikula\ZAuthBundle\Form\Type\ChangePasswordType;
use Zikula\ZAuthBundle\Form\Type\RegistrationType;
use Zikula\ZAuthBundle\Form\Type\VerifyRegistrationType;
use Zikula\ZAuthBundle\Form\Type\ZAuthDuplicatePassType;
use Zikula\ZAuthBundle\Twig\Extension\LegacyRegistrationExtension;
use Zikula\ZAuthBundle\Validator\Constraints\ValidAntiSpamAnswerValidator;
use Zikula\ZAuthBundle\Validator\Constraints\ValidPasswordValidator;

class ZikulaZAuthExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $mailVerificationRequired = $config['registration']['email_verification_required'];
        $minimumPasswordLength = $config['credentials']['minimum_password_length'];
        $usePasswordStrengthMeter = $config['credentials']['use_password_strength_meter'];

        $container->getDefinition(UserCreationApi::class)
            ->setArgument('$mailVerificationRequired', $mailVerificationRequired);
        $container->getDefinition(NativeEitherAuthenticationMethod::class)
            ->setArgument('$mailVerificationRequired', $mailVerificationRequired);
        $container->getDefinition(NativeEmailAuthenticationMethod::class)
            ->setArgument('$mailVerificationRequired', $mailVerificationRequired);
        $container->getDefinition(NativeUnameAuthenticationMethod::class)
            ->setArgument('$mailVerificationRequired', $mailVerificationRequired);
        $container->getDefinition(RegistrationListener::class)
            ->setArgument('$mailVerificationRequired', $mailVerificationRequired);
        $container->getDefinition(AdminCreatedUserType::class)
            ->setArgument('$mailVerificationRequired', $mailVerificationRequired)
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength);
        $container->getDefinition(AdminModifyUserType::class)
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength);
        $container->getDefinition(ChangePasswordType::class)
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength);
        $container->getDefinition(RegistrationType::class)
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength)
            ->setArgument('$antiSpamQuestion', $config['registration']['antispam_question']['question']);
        $container->getDefinition(VerifyRegistrationType::class)
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength);
        $container->getDefinition(ZAuthDuplicatePassType::class)
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength);

        $container->getDefinition(AccountController::class)
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength)
            ->setArgument('$usePasswordStrengthMeter', $usePasswordStrengthMeter)
            ->setArgument('$changeEmailExpireDays', $config['credentials']['change_email_expire_days'])
            ->setArgument('$changePasswordExpireDays', $config['credentials']['change_password_expire_days'])
            ->setArgument('$loginDisplayInactiveStatus', $config['login']['display_inactive_status'])
            ->setArgument('$loginDisplayPendingStatus', $config['login']['display_pending_status']);
        $container->getDefinition(FileIOController::class)
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength);
        $container->getDefinition(RegistrationController::class)
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength)
            ->setArgument('$usePasswordStrengthMeter', $usePasswordStrengthMeter);
        $container->getDefinition(UserAdministrationController::class)
            ->setArgument('$usersPerPage', $config['users_per_page'])
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength)
            ->setArgument('$usePasswordStrengthMeter', $usePasswordStrengthMeter)
            ->setArgument('$changePasswordExpireDays', $config['credentials']['change_password_expire_days']);

        $container->getDefinition(DeletePendingRegistrationsListener::class)
            ->setArgument('$registrationExpireDays', $config['registration']['registration_expire_days']);

        $container->getDefinition(LegacyRegistrationExtension::class)
            ->setArgument('$mailVerificationRequired', $mailVerificationRequired)
            ->setArgument('$usePasswordStrengthMeter', $usePasswordStrengthMeter);

        $container->getDefinition(ValidAntiSpamAnswerValidator::class)
            ->setArgument('$antiSpamAnswer', $config['registration']['antispam_question']['answer']);
        $container->getDefinition(ValidPasswordValidator::class)
            ->setArgument('$requireNonCompromisedPassword', $config['credentials']['require_non_compromised_password'])
            ->setArgument('$minimumPasswordLength', $minimumPasswordLength);
    }
}
