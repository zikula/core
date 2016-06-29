<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ZAuthModule\Validator\Constraints\ValidAntiSpamAnswer;
use Zikula\ZAuthModule\Validator\Constraints\ValidEmail;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\Validator\Constraints\ValidPasswordReminder;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\ZAuthModule\ZAuthConstant;

class RegistrationType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array
     */
    private $zAuthModVars;

    /**
     * @var array
     */
    private $usersModVars;

    /**
     * RegistrationType constructor.
     * @param TranslatorInterface $translator
     * @param VariableApi $variableApi
     */
    public function __construct(TranslatorInterface $translator, VariableApi $variableApi)
    {
        $this->translator = $translator;
        $this->usersModVars = $variableApi->getAll('ZikulaUsersModule');
        $this->zAuthModVars = $variableApi->getAll('ZikulaZAuthModule');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->translator->__('User name'),
                'help' => $this->translator->__('User names can contain letters, numbers, underscores, periods, spaces and/or dashes.'),
                'constraints' => [new ValidUname()]
            ])
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
                'first_options' => [
                    'label' => $this->translator->__('Email'),
                    'help' => $this->translator->__('You will use your e-mail address to identify yourself when you log in.'),
                ],
                'second_options' => ['label' => $this->translator->__('Repeat Email')],
                'invalid_message' => $this->translator->__('The emails  must match!'),
                'constraints' => [new ValidEmail()]
            ])
            ->add('pass', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                'first_options' => ['label' => $this->translator->__('Password')],
                'second_options' => ['label' => $this->translator->__('Repeat Password')],
                'invalid_message' => $this->translator->__('The passwords must match!'),
                'constraints' => [new ValidPassword()]
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->translator->__('Save'),
                'icon' => 'fa-plus',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\ButtonType', [
                'label' => $this->translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-danger']
            ])
            ->add('reset', 'Symfony\Component\Form\Extension\Core\Type\ResetType', [
                'label' => $this->translator->__('Reset'),
                'icon' => 'fa-refresh',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
        if ($options['passwordReminderEnabled']) {
            $builder
                ->add('passreminder', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'required' => $options['passwordReminderMandatory'],
                    'constraints' => [new ValidPasswordReminder()],
                    'help' => $this->translator->__('Enter a word or a phrase that will remind you of your password.'),
                    'alert' => [$this->translator->__('Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!') => 'info'],
                ])
            ;
        }
        if (!empty($options['antiSpamQuestion'])) {
            $builder->add('antispamanswer', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'mapped' => false,
                'label' => $options['antiSpamQuestion'],
                'constraints' => new ValidAntiSpamAnswer(),
                'help' => $this->translator->__('Asking this question helps us prevent automated scripts from accessing private areas of the site.'),
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_registration';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'passwordReminderEnabled' => $this->zAuthModVars[ZAuthConstant::MODVAR_PASSWORD_REMINDER_ENABLED],
            'passwordReminderMandatory' => $this->zAuthModVars[ZAuthConstant::MODVAR_PASSWORD_REMINDER_MANDATORY],
            'antiSpamQuestion' => $this->usersModVars[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION],
        ]);
    }
}
