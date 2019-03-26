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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class LostPasswordType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['includeReset']) {
            $builder
                ->add('uname', TextType::class, [
                    'required' => false,
                    'label' => $this->__('User name'),
                    'input_group' => ['left' => '<i class="fa fa-user"></i>'],
                ])
                ->add('email', EmailType::class, [
                    'required' => false,
                    'label' => $this->__('Email Address'),
                    'input_group' => ['left' => '<i class="fa fa-at"></i>'],
                ])
            ;
        } else {
            $builder
                ->add('pass', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => $this->__('Create new password'),
                        'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
                    ],
                    'second_options' => [
                        'label' => $this->__('Repeat new password'),
                        'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
                    ],
                    'invalid_message' => $this->__('The passwords must match!'),
                    'constraints' => [
                        new NotNull(),
                        new ValidPassword()
                    ]
                ])
            ;
        }
        $builder
            ->add('submit', SubmitType::class, [
                'label' => $this->__('Submit'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_account_lostpassword';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'includeReset' => false,
            'constraints' => new Callback(['callback' => function($data, ExecutionContextInterface $context) {
                if (!isset($data['pass']) && empty($data['uname']) && empty($data['email'])) {
                    $context
                        ->buildViolation('Error! You must enter either your username or email address.')
                        ->addViolation()
                    ;
                }
            }]),
        ]);
    }
}
