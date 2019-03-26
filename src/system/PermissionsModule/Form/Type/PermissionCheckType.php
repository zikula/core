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

namespace Zikula\PermissionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class PermissionCheckType extends AbstractType
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
            ->add('user', TextType::class, [
                'label' => $this->__('User name'),
                'required' => false
            ])
            ->add('component', TextType::class, [
                'label' => $this->__('Component to check'),
                'data' => '.*'
            ])
            ->add('instance', TextType::class, [
                'label' => $this->__('Instance to check'),
                'data' => '.*'
            ])
            ->add('level', ChoiceType::class, [
                'label' => $this->__('Permission level'),
                'choices' => array_flip($options['permissionLevels']),
                'data' => ACCESS_READ
            ])
            ->add('check', ButtonType::class, [
                'label' => $this->__('Check permission'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->add('reset', ButtonType::class, [
                'label' => $this->__('Reset'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-danger'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulapermissionsmodule_permissioncheck';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['id' => 'testpermform'],
            'permissionLevels' => []
        ]);
    }
}
