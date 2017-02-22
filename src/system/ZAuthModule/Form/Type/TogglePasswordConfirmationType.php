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

class TogglePasswordConfirmationType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('toggle', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['mustChangePass'] ? $options['translator']->__('Yes, cancel the change of password') : $options['translator']->__('Yes, force the change of password'),
                'icon' => $options['mustChangePass'] ? 'fa-times' : 'fa-refresh',
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
        return 'zikulazauthmodule_togglepassconfirmation';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'mustChangePass' => true
        ]);
    }
}
