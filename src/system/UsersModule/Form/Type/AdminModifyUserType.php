<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\UsersModule\Constant;

class AdminModifyUserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('activated', ChoiceType::class, [
                'choices' => [
                    $options['translator']->__('Active') => Constant::ACTIVATED_ACTIVE,
                    $options['translator']->__('Inactive') => Constant::ACTIVATED_INACTIVE,
                    $options['translator']->__('Pending') => Constant::ACTIVATED_PENDING_REG
                ],
                'choices_as_values' => true,
                'label' => $options['translator']->__('User status')
            ])
            ->add('groups', EntityType::class, [
                'class' => 'ZikulaGroupsModule:GroupEntity',
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['translator']->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulausersmodule_adminmodifyuser';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}
