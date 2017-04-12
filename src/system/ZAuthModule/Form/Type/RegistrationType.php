<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\ZAuthModule\Validator\Constraints\ValidAntiSpamAnswer;
use Zikula\ZAuthModule\Validator\Constraints\ValidEmail;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
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
     * RegistrationType constructor.
     *
     * @param TranslatorInterface $translator
     * @param VariableApiInterface $variableApi
     */
    public function __construct(TranslatorInterface $translator, VariableApiInterface $variableApi)
    {
        $this->translator = $translator;
        $this->zAuthModVars = $variableApi->getAll('ZikulaZAuthModule');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => $this->translator->__('User name'),
                'help' => $this->translator->__('User names can contain letters, numbers, underscores, periods, spaces and/or dashes.'),
                'constraints' => [new ValidUname()]
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => $this->translator->__('Email'),
                    'help' => $this->translator->__('You will use your e-mail address to identify yourself when you log in.'),
                ],
                'second_options' => ['label' => $this->translator->__('Repeat Email')],
                'invalid_message' => $this->translator->__('The emails  must match!'),
                'constraints' => [new ValidEmail()]
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => $this->translator->__('Password')],
                'second_options' => ['label' => $this->translator->__('Repeat Password')],
                'invalid_message' => $this->translator->__('The passwords must match!'),
                'constraints' => [
                    new NotNull(),
                    new ValidPassword()
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->__('Save'),
                'icon' => 'fa-plus',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', ButtonType::class, [
                'label' => $this->translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-danger']
            ])
            ->add('reset', ResetType::class, [
                'label' => $this->translator->__('Reset'),
                'icon' => 'fa-refresh',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
        if (!empty($options['antiSpamQuestion'])) {
            $builder->add('antispamanswer', TextType::class, [
                'mapped' => false,
                'label' => $options['antiSpamQuestion'],
                'constraints' => new ValidAntiSpamAnswer(),
                'help' => $this->translator->__('Asking this question helps us prevent automated scripts from accessing private areas of the site.'),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_registration';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'antiSpamQuestion' => $this->zAuthModVars[ZAuthConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION]
        ]);
    }
}
