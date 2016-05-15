<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\Account\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;

class ChangeEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
                'first_options' => [
                    'label' => $options['translator']->__('New email address'),
                ],
                'second_options' => ['label' => $options['translator']->__('Repeat new email address')],
                'invalid_message' => $options['translator']->__('The emails  must match!'),
                'constraints' => [new ValidEmail()]
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Submit'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_account_changeemail';
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
