<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', ['mapped' => false])
            ->add('title', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('options', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'help' => 'json-formatted array (for now).',
                'label' => 'options',
                'required' => false
            ])
//            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
//                'label' => $options['translator']->__('Save'),
//                'icon' => 'fa-check',
//                'attr' => [
//                    'class' => 'btn btn-success'
//                ]
//            ])
//            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
//                'label' => $options['translator']->__('Cancel'),
//                'icon' => 'fa-times',
//                'attr' => [
//                    'class' => 'btn btn-default'
//                ]
//            ])
        ;
        // this is maybe a temporary workaround for enumerating the options for a MenuItem.
        $builder->get('options')
            ->addModelTransformer(new CallbackTransformer(
                function ($optionsAsArray) {
                    // transform the array to a string
                    return json_encode($optionsAsArray);
                },
                function ($optionsAsString) {
                    // transform the string back to an array
                    return json_decode($optionsAsString, true);
                }
            ))
        ;
        $menuItemEntity = $builder->getData();
        if ($options['includeRoot']) {
            $builder->add('root', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'class' => 'Zikula\MenuModule\Entity\MenuItemEntity',
                'choice_label' => 'title',
                'placeholder' => $options['translator']->__('No root'),
                'empty_data' => null,
                'required' => false,
            ]);
        } else {
            $builder->add('root', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'data' => $menuItemEntity->getRoot()->getId(),
                'mapped' => false
            ]);
        }
        if ($options['includeParent']) {
            $builder->add('parent', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'class' => 'Zikula\MenuModule\Entity\MenuItemEntity',
                'choice_label' => 'title',
                'placeholder' => $options['translator']->__('No parent'),
                'empty_data' => null,
                'required' => false,
            ]);
        } else {
            $builder->add('parent', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'data' => $menuItemEntity->getParent()->getId(),
                'mapped' => false
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulamenumodule_menuitem';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'includeRoot' => false,
            'includeParent' => false,
            'data_class' => 'Zikula\MenuModule\Entity\MenuItemEntity',
        ]);
    }

}
