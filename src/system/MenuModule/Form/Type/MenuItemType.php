<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\MenuModule\Entity\MenuItemEntity;
use Zikula\MenuModule\Form\DataTransformer\KeyValueTransformer;
use Zikula\MenuModule\Form\EventListener\KeyValueFixerListener;
use Zikula\MenuModule\Form\EventListener\OptionValidatorListener;

class MenuItemType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('options', CollectionType::class, [
                'entry_type' => KeyValuePairType::class,
                'entry_options'  => [
                    'key_options' => [
                        'choices' => $this->getKeyChoices()
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
            ->add('after', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ])
        ;
        $builder->get('options')
            ->addModelTransformer(new KeyValueTransformer())
            ->addEventSubscriber(new KeyValueFixerListener())
            ->addEventSubscriber(new OptionValidatorListener($this->translator))
        ;
        if ($options['includeRoot']) {
            $builder->add('root', EntityType::class, [
                'label' => $this->__('Root'),
                'class' => MenuItemEntity::class,
                'choice_label' => 'title'
            ]);
        } else {
            $builder->add('root', HiddenMenuItemType::class);
        }
        if ($options['includeParent']) {
            $builder->add('parent', EntityType::class, [
                'class' => MenuItemEntity::class,
                'choice_label' => 'title',
                'placeholder' => $this->__('No parent'),
                'empty_data' => null,
                'required' => false,
            ]);
        } else {
            $builder->add('parent', HiddenMenuItemType::class);
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
            'includeRoot' => false,
            'includeParent' => false,
            'data_class' => MenuItemEntity::class,
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
