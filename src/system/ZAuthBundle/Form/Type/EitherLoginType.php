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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;

class EitherLoginType extends AbstractType
{
    public function __construct(private readonly RouterInterface $router, private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $forgotUsername = $this->translator->trans('I forgot my username');
        $forgotPassword = $this->translator->trans('I forgot my password');
        $builder
            ->add('either', TextType::class, [
                'label' => 'User name or email',
                /** @Ignore */
                'help' => '<a href="' . $this->router->generate('zikulazauthbundle_account_lostusername') . '">' . $forgotUsername . '</a>',
                'help_html' => true,
                'input_group' => ['left' => '<i class="fas fa-fw fa-sign-in-alt"></i>'],
            ])
            ->add('pass', PasswordType::class, [
                'label' => 'Password',
                /** @Ignore */
                'help' => '<a href="' . $this->router->generate('zikulazauthbundle_account_lostpassword') . '">' . $forgotPassword . '</a>',
                'help_html' => true,
                'input_group' => ['left' => '<i class="fas fa-fw fa-key"></i>'],
            ])
            ->add('rememberme', CheckboxType::class, [
                'required' => false,
                'label' => 'Remember me',
                'label_attr' => ['class' => 'switch-custom'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Login',
                'icon' => 'fa-angle-double-right',
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'zikulazauthbundle_authentication_either';
    }
}
