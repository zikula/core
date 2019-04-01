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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\PermissionsModule\Entity\PermissionEntity;

class PermissionType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pid', HiddenType::class)
            ->add('gid', ChoiceType::class, [
                'label' => $this->__('Group'),
                'choices' => array_flip($options['groups'])
            ])
            ->add('sequence', HiddenType::class)
            ->add('component', TextType::class, [
                'label' => $this->__('Component')
            ])
            ->add('instance', TextType::class, [
                'label' => $this->__('Instance')
            ])
            ->add('level', ChoiceType::class, [
                'label' => $this->__('Level'),
                'choices' => array_flip($options['permissionLevels'])
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulapermissionsmodule_permission';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PermissionEntity::class,
            'groups' => [],
            'permissionLevels' => []
        ]);
    }
}
