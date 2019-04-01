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

namespace Zikula\CategoriesModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\CategoriesModule\Builder\EntitySelectionBuilder;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Class CategoryRegistryType
 * @see http://symfony.com/doc/current/form/dynamic_form_modification.html#dynamic-generation-for-submitted-forms
 */
class CategoryRegistryType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var EntitySelectionBuilder
     */
    private $entitySelectionBuilder;

    public function __construct(TranslatorInterface $translator, EntitySelectionBuilder $entitySelectionBuilder)
    {
        $this->setTranslator($translator);
        $this->entitySelectionBuilder = $entitySelectionBuilder;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('modname', ChoiceType::class, [
                'label' => $this->__('Module'),
                'choices' => $options['categorizableModules'],
                'placeholder' => $this->__('Select module')
            ])
            ->add('property', TextType::class, [
                'label' => $this->__('Property name'),
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('category', CategoryTreeType::class, [
                'label' => $this->__('Category')
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;

        $translator = $this->translator;
        $formModifier = function(FormInterface $form, string $modName = null) use ($translator) {
            $entities = null === $modName ? [] : $this->entitySelectionBuilder->buildFor($modName);
            $form->add('entityname', ChoiceType::class, [
                'label' => $translator->__('Entity'),
                'choices' => $entities
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function(FormEvent $event) use ($formModifier) {
                /** @var CategoryRegistryEntity $data */
                $data = $event->getData();
                $formModifier($event->getForm(), $data->getModname());
            }
        );

        $builder->get('modname')->addEventListener(
            FormEvents::POST_SUBMIT,
            static function(FormEvent $event) use ($formModifier) {
                $modName = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $modName);
            }
        );
    }

    public function getBlockPrefix()
    {
        return 'zikulacategoriesmodule_category_registry';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'categorizableModules' => []
        ]);
    }
}
