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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class EmailLoginType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => $this->trans('Email address'),
                'input_group' => ['left' => '<i class="fa fa-fw fa-at"></i>']
            ])
            ->add('pass', PasswordType::class, [
                'label' => $this->trans('Password'),
                'input_group' => ['left' => '<i class="fa fa-fw fa-key"></i>']
            ])
            ->add('rememberme', CheckboxType::class, [
                'required' => false,
                'label' => $this->trans('Remember me'),
                'label_attr' => ['class' => 'switch-custom']
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('Login'),
                'icon' => 'fa-angle-double-right',
                'attr' => ['class' => 'btn btn-success']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_authentication_email';
    }
}
