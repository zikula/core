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
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;

class ChangeEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => 'New email address',
                ],
                'second_options' => [
                    'label' => 'Repeat new email address'
                ],
                'invalid_message' => 'The emails must match!',
                'constraints' => [
                    new ValidEmail()
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_account_changeemail';
    }
}
