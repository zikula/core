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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\Common\Translator\TranslatorInterface;

class UnameLoginType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * UnameLoginType constructor.
     *
     * @param $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => $this->translator->__('User name'),
                'input_group' => ['left' => '<i class="fa fa-user fa-fw"></i>']
            ])
            ->add('pass', PasswordType::class, [
                'label' => $this->translator->__('Password'),
                'input_group' => ['left' => '<i class="fa fa-key fa-fw"></i>']
            ])
            ->add('rememberme', CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->__('Remember me'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->__('Login'),
                'icon' => 'fa-angle-double-right',
                'attr' => ['class' => 'btn btn-success']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_authentication_uname';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => new IdentityTranslator()
        ]);
    }
}
