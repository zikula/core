<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminModifyUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'Zikula\UsersModule\Form\Type\UserType', [
                'data_class' => 'Zikula\UsersModule\Entity\UserEntity',
                'translator' => $options['translator'],
                'passwordReminderEnabled' => false,
                'passwordReminderMandatory' => false,
                'allowNullPassword' => true
            ])
            ->add('setpass', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'required' => false,
                'mapped' => false,
                'label' => $options['translator']->__('Set password now'),
            ])
            ->add('activated', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => [
                    $options['translator']->__('Active') => 1,
                    $options['translator']->__('Inactive') => 0
                ],
                'choices_as_values' => true,
                'label' => $options['translator']->__('User status')
            ])
            ->add('groups', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'class' => 'ZikulaGroupsModule:GroupEntity',
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
        $builder->get('user')->get('pass')->setRequired(false);
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_adminmodifyuser';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
        ]);
    }
}
