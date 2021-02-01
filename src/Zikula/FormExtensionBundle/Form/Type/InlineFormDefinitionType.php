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

namespace Zikula\Bundle\FormExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\Bundle\FormExtensionBundle\DynamicFieldsContainerInterface;

/**
 * Form type for embedding dynamic fields.
 */
class InlineFormDefinitionType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var DynamicFieldsContainerInterface
     */
    private $dynamicFieldsContainer;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $this->dynamicFieldsContainer = $options['dynamicFieldsContainer'];

        if (null === $this->dynamicFieldsContainer || !($this->dynamicFieldsContainer instanceof DynamicFieldsContainerInterface)) {
            return;
        }

        foreach ($this->dynamicFieldsContainer->getDynamicFieldsSpecification() as $fieldSpecification) {
            $fieldOptions = $fieldSpecification->getFormOptions();
            $fieldOptions['label'] = $fieldOptions['label'] ?? $fieldSpecification->getLabel($this->translator->getLocale());

            $prefix = $fieldSpecification->getPrefix();
            $prefix = null !== $prefix && '' !== $prefix ? $prefix . ':' : '';

            $builder->add($prefix . $fieldSpecification->getName(), $fieldSpecification->getFormType(), $fieldOptions);
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulaformextensionbundle_inlineformdefinition';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [],
            'mapped' => false,
            'dynamicFieldsContainer' => null
        ]);
    }
}
