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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\MenuModule\Form\DataTransformer\KeyValueTransformer;
use Zikula\MenuModule\Form\EventListener\KeyValueFixerListener;
use Zikula\MenuModule\Form\EventListener\OptionValidatorListener;

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
            ->add('options', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'entry_type' => 'Zikula\MenuModule\Form\Type\KeyValuePairType',
                'entry_options'  => [
                    'key_options' => [
                        'choices' => $this->getKeyChoices(),
                        'choices_as_values' => true,
                    ],
                    'value_options' => [
                        'required' => false,
                    ]
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => 'Options',
                'required' => false
            ])
            ->add('after', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'mapped' => false,
                'required' => false
            ])
        ;
        $builder->get('options')
            ->addModelTransformer(new KeyValueTransformer())
            ->addEventSubscriber(new KeyValueFixerListener())
            ->addEventSubscriber(new OptionValidatorListener())
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

    private function getKeyChoices()
    {
        return [
            'route' => 'route',
            'routeParameters*' => 'routeParameters',
            'uri' => 'uri',
            'label' => 'label',
            'attributes*' => 'attributes',
            'linkAttributes*' => 'linkAttributes',
            'childrenAttributes*' => 'childrenAttributes',
            'labelAttributes*' => 'labelAttributes',
            'extras*' => 'extras',
            'current' => 'current',
            'display+' => 'display',
            'displayChildren+' => 'displayChildren',
        ];
    }
}
