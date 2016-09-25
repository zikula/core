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
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MenuItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'constraints' => [new NotBlank()]
            ])
            ->add('options', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'help' => 'json-formatted array (for now).',
                'label' => 'options',
                'required' => false
            ])
            ->add('after', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'mapped' => false,
                'required' => false
            ])
        ;
        // this is maybe a temporary workaround for enumerating the options for a MenuItem.
        $builder->get('options')
            ->addModelTransformer(new CallbackTransformer(
                function ($optionsAsArray) {
                    // transform the array to a string
                    return json_encode($optionsAsArray);
                },
                function ($optionsAsString) use ($options) {
                    // transform the string back to an array
                    if (!$string = json_decode($optionsAsString, true)) {
                        throw new TransformationFailedException($options['translator']->__('Invalid data: cannot json_decode provided string.'));
                    }

                    return $string;
                }
            ))
        ;
        if ($options['includeRoot']) {
            $builder->add('root', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'class' => 'Zikula\MenuModule\Entity\MenuItemEntity',
                'choice_label' => 'title',
            ]);
        } else {
            $builder->add('root', 'Zikula\MenuModule\Form\Type\HiddenMenuItemType');
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
            $builder->add('parent', 'Zikula\MenuModule\Form\Type\HiddenMenuItemType');
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
