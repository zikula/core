<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesBundle\Form\Type;

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
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\CategoriesBundle\Entity\CategoryRegistryEntity;
use Zikula\CategoriesBundle\Helper\CategorizableBundleHelper;

/**
 * @see https://symfony.com/doc/current/form/dynamic_form_modification.html#dynamic-generation-for-submitted-forms
 */
class CategoryRegistryType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator, private readonly CategorizableBundleHelper $categorizableBundleHelper)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bundleName', ChoiceType::class, [
                'label' => 'Bundle',
                'choices' => /** @Ignore */ $options['categorizableBundles'],
                'placeholder' => 'Select bundle',
            ])
            ->add('property', TextType::class, [
                'label' => 'Property name',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('category', CategoryTreeType::class, [
                'label' => 'Category'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
            ])
        ;

        $translator = $this->translator;
        $formModifier = function (FormInterface $form, string $bundleName = null) use ($translator) {
            $entities = null === $bundleName ? [] : $this->categorizableBundleHelper->buildEntityChoiceListFor($bundleName);
            $form->add('entityname', ChoiceType::class, [
                /** @Ignore */
                'label' => $translator->trans('Entity'),
                'choices' => /** @Ignore */ $entities,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event) use ($formModifier) {
                /** @var CategoryRegistryEntity $data */
                $data = $event->getData();
                $formModifier($event->getForm(), $data->getBundleName());
            }
        );

        $builder->get('bundleName')->addEventListener(
            FormEvents::POST_SUBMIT,
            static function (FormEvent $event) use ($formModifier) {
                $bundleName = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $bundleName);
            }
        );
    }

    public function getBlockPrefix(): string
    {
        return 'zikulacategoriesbundle_category_registry';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'categorizableBundles' => [],
        ]);
    }
}
