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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;

class UnameLoginType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => 'User name',
                'help' => '<a href="' . $this->router->generate('zikulazauthmodule_account_lostusername') . '">I forgot my username</a>',
                'help_html' => true,
                'input_group' => ['left' => '<i class="fas fa-fw fa-user"></i>']
            ])
            ->add('pass', PasswordType::class, [
                'label' => 'Password',
                'help' => '<a href="' . $this->router->generate('zikulazauthmodule_account_lostpassword') . '">I forgot my password</a>',
                'help_html' => true,
                'input_group' => ['left' => '<i class="fas fa-fw fa-key"></i>']
            ])
            ->add('rememberme', CheckboxType::class, [
                'required' => false,
                'label' => 'Remember me',
                'label_attr' => ['class' => 'switch-custom']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Login',
                'icon' => 'fa-angle-double-right',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_authentication_uname';
    }
}
