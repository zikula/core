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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;

class EmailLoginType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * EmailLoginType constructor.
     *
     * @param $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => $this->translator->__('Email address'),
                'input_group' => ['left' => '<i class="fa fa-at fa-fw"></i>']
            ])
            ->add('pass', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => $this->translator->__('Password'),
                'input_group' => ['left' => '<i class="fa fa-key fa-fw"></i>']
            ])
            ->add('rememberme', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'label' => $this->translator->__('Remember me'),
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->translator->__('Login'),
                'icon' => 'fa-angle-double-right',
                'attr' => ['class' => 'btn btn-success']
            ])
        ;
    }

    /**
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_authentication_email';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // @todo can be removed?
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}
