<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\RegistrationType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApproveRegistrationConfirmationType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('force', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('confirm', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['buttonLabel'],
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success'],
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    /**
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulausersmodule_approveregistrationconfirmation';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'buttonLabel' => ''
        ]);
    }
}
