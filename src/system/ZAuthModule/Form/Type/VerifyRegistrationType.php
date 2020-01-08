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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\Validator\Constraints\ValidRegistrationVerification;

class VerifyRegistrationType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
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
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'string'])
                ]
            ])
            ->add('verifycode', TextType::class, [
                'label' => $this->trans('Verification code'),
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'string'])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('Submit'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
        ;
        if ($options['setpass']) {
            $builder->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => $this->trans('Password')],
                'second_options' => ['label' => $this->trans('Repeat Password')],
                'invalid_message' => $this->trans('The passwords must match!'),
                'constraints' => [
                    new NotNull(),
                    new ValidPassword()
                ]
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_verifyregistration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'setpass' => true,
            'constraints' => [
                new ValidRegistrationVerification()
            ]
        ]);
    }
}
