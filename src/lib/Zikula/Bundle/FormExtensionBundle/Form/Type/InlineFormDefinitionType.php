<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Bundle\FormExtensionBundle\DynamicFieldsContainerInterface;
use Zikula\Common\Translator\Translator;

/**
 * Form type for embedding dynamic fields.
 */
class InlineFormDefinitionType extends AbstractType
{
    /**
     * @var Translator
     */
    private $translator = null;

    /**
     * @var DynamicFieldsContainerInterface
     */
    private $dynamicFieldsContainer = null;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $this->translator = $options['translator'];
        $this->dynamicFieldsContainer = $options['dynamicFieldsContainer'];

        if (null === $this->dynamicFieldsContainer) {
            return;
        }

        foreach ($this->dynamicFieldsContainer->getDynamicFieldsSpecification() as $fieldSpecification) {
            $options = $fieldSpecification->getFormOptions();
            $options['label'] = isset($options['label']) ? $options['label'] : $fieldSpecification->getLabel($this->translator->getLocale());

            $prefix = $fieldSpecification->getPrefix();
            $prefix = null !== $prefix && '' != $prefix ? $prefix . ':' : '';

            $builder->add($prefix . $fieldSpecification->getName(), $fieldSpecification->getFormType(), $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulaformextensionbundle_inlineformdefinition';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'constraints' => [],
            'mapped' => false,
            'dynamicFieldsContainer' => null
        ]);
    }
}
