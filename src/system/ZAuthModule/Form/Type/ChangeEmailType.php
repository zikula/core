<?php

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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;

class ChangeEmailType extends AbstractType
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
        $builder
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => $this->__('New email address'),
                ],
                'second_options' => [
                    'label' => $this->__('Repeat new email address')
                ],
                'invalid_message' => $this->__('The emails  must match!'),
                'constraints' => [
                    new ValidEmail()
                ]
            ])
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
        return 'zikulazauthmodule_account_changeemail';
    }
}
