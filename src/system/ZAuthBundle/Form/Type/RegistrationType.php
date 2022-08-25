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

namespace Zikula\ZAuthBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Translation\Extractor\Annotation\Ignore;
use Zikula\UsersBundle\UsersConstant;
use Zikula\UsersBundle\Validator\Constraints\ValidEmail;
use Zikula\UsersBundle\Validator\Constraints\ValidUname;
use Zikula\ZAuthBundle\Validator\Constraints\ValidAntiSpamAnswer;
use Zikula\ZAuthBundle\Validator\Constraints\ValidPassword;

class RegistrationType extends AbstractType
{
    public function __construct(private readonly int $minimumPasswordLength, private readonly ?string $antiSpamQuestion)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => 'User name',
                'help' => 'User names can contain letters, numbers, underscores, periods, spaces and/or dashes.',
                'attr' => [
                    'maxlength' => UsersConstant::UNAME_VALIDATION_MAX_LENGTH,
                ],
                'constraints' => [
                    new ValidUname(),
                ],
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => 'Email',
                    'help' => 'You will use your e-mail address to identify yourself when you log in.',
                ],
                'second_options' => [
                    'label' => 'Repeat email',
                ],
                'invalid_message' => 'The emails must match!',
                'constraints' => [
                    new ValidEmail(),
                ],
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'class' => 'pwstrength',
                        'data-uname-id' => $builder->getName() . '_' . $builder->get('uname')->getName(),
                        'minlength' => $options['minimumPasswordLength'],
                        'pattern' => '.{' . $options['minimumPasswordLength'] . ',}',
                    ],
                    'label' => 'Password',
                    'help' => 'Minimum password length: %amount% characters. Longer passwords are more secure.',
                    'help_translation_parameters' => [
                        '%amount%' => $options['minimumPasswordLength'],
                    ],
                ],
                'second_options' => [
                    'label' => 'Repeat password',
                ],
                'invalid_message' => 'The passwords must match!',
                'constraints' => [
                    new NotNull(),
                    new ValidPassword(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-plus',
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
                'validate' => false,
                'attr' => [
                    'class' => 'btn-danger',
                ],
            ])
            ->add('reset', ResetType::class, [
                'label' => 'Reset',
                'icon' => 'fa-refresh',
            ])
        ;
        if (!empty($options['antiSpamQuestion'])) {
            $builder->add('antispamanswer', TextType::class, [
                'mapped' => false,
                /** @Ignore */
                'label' => $options['antiSpamQuestion'],
                'constraints' => new ValidAntiSpamAnswer(),
                'help' => 'Asking this question helps us prevent automated scripts from accessing private areas of the site.',
            ]);
        }
    }

    public function getBlockPrefix(): string
    {
        return 'zikulazauthbundle_registration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'minimumPasswordLength' => $this->minimumPasswordLength,
            'antiSpamQuestion' => $this->antiSpamQuestion,
        ]);
    }
}
