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

class DeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'choice_attr' => function() {
                    return ['class' => 'user-checkboxes'];
                },
                'class' => 'ZikulaUsersModule:UserEntity',
                'choices' => $options['choices'],
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'uname'
            ])
            ->add('delete', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Delete selected users'),
                'icon' => 'fa-trash-o',
                'attr' => ['class' => 'btn btn-danger'],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_delete';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'choices' => [],
            'attr' => ['id' => 'users_searchresults']
        ]);
    }
}
