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
use Zikula\UsersModule\Validator\Constraints\ValidEmail;
use Zikula\UsersModule\Validator\Constraints\ValidUname;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('User name'),
                'help' => $options['translator']->__('User names can contain letters, numbers, underscores, periods, spaces and/or dashes.'),
                'constraints' => [new ValidUname()]
            ])
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
                'first_options' => [
                    'label' => $options['translator']->__('Email'),
                    'help' => $options['translator']->__('You will use your e-mail address to identify yourself when you log in.'),
                ],
                'second_options' => ['label' => $options['translator']->__('Repeat Email')],
                'invalid_message' => $options['translator']->__('The emails  must match!'),
                'constraints' => [new ValidEmail()]
            ])
            // theme - deprecated
            // time zone
            // locale i18n
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_user';
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
