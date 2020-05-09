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

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Validator\Constraints\ValidUserFields;
use Zikula\ZAuthModule\ZAuthConstant;

class AdminModifyUserType extends AbstractType
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(VariableApiInterface $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => 'User name',
                'help' => 'User names can contain letters, numbers, underscores, periods, spaces and/or dashes.',
                'constraints' => [
                    new ValidUname()
                ]
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => 'Email',
                ],
                'second_options' => [
                    'label' => 'Repeat email'
                ],
                'invalid_message' => 'The emails must match!',
                'constraints' => [
                    new ValidEmail()
                ]
            ])
            ->add('setpass', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Set password now',
                'label_attr' => ['class' => 'switch-custom']
            ])
            ->add('pass', ZAuthDuplicatePassType::class, [
                'label' => 'Password',
                'data_class' => AuthenticationMappingEntity::class,
                'dataUnameId' => $builder->getName() . '_' . $builder->get('uname')->getName()
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times'
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_adminmodifyuser';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'minimumPasswordLength' => $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::PASSWORD_MINIMUM_LENGTH),
            'constraints' => [
                new ValidUserFields()
            ]
        ]);
    }
}
