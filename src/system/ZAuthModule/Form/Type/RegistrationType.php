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

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\ZAuthModule\Validator\Constraints\ValidAntiSpamAnswer;
use Zikula\ZAuthModule\Validator\Constraints\ValidEmail;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\ZAuthConstant;

class RegistrationType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var array
     */
    private $zAuthModVars;

    public function __construct(
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->setTranslator($translator);
        $this->zAuthModVars = $variableApi->getAll('ZikulaZAuthModule');
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => $this->trans('User name'),
                'help' => $this->trans('User names can contain letters, numbers, underscores, periods, spaces and/or dashes.'),
                'attr' => [
                    'maxlength' => UsersConstant::UNAME_VALIDATION_MAX_LENGTH
                ],
                'constraints' => [new ValidUname()]
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => $this->trans('Email'),
                    'help' => $this->trans('You will use your e-mail address to identify yourself when you log in.'),
                ],
                'second_options' => [
                    'label' => $this->trans('Repeat Email')
                ],
                'invalid_message' => $this->trans('The emails  must match!'),
                'constraints' => [new ValidEmail()]
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => $this->trans('Password'),
                    'help' => $this->trans('Minimum password length: %amount% characters.', ['%amount%' => $options['minimumPasswordLength']])
                ],
                'second_options' => [
                    'label' => $this->trans('Repeat Password')
                ],
                'invalid_message' => $this->trans('The passwords must match!'),
                'constraints' => [
                    new NotNull(),
                    new ValidPassword()
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('Save'),
                'icon' => 'fa-plus',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', ButtonType::class, [
                'label' => $this->trans('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-danger']
            ])
            ->add('reset', ResetType::class, [
                'label' => $this->trans('Reset'),
                'icon' => 'fa-refresh',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
        if (!empty($options['antiSpamQuestion'])) {
            $builder->add('antispamanswer', TextType::class, [
                'mapped' => false,
                'label' => $options['antiSpamQuestion'],
                'constraints' => new ValidAntiSpamAnswer(),
                'help' => $this->trans('Asking this question helps us prevent automated scripts from accessing private areas of the site.')
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_registration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'minimumPasswordLength' => $this->zAuthModVars[ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH],
            'antiSpamQuestion' => $this->zAuthModVars[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION]
        ]);
    }
}
