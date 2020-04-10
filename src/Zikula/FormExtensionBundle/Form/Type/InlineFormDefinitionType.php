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
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
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
            $this->removeChoiceLoader($fieldSpecification->getFormType(), $fieldOptions);

            $prefix = $fieldSpecification->getPrefix();
            $prefix = null !== $prefix && '' !== $prefix ? $prefix . ':' : '';

            $builder->add($prefix . $fieldSpecification->getName(), $fieldSpecification->getFormType(), $fieldOptions);
        }
    }

    /**
     * Symfony 4 requires the choice_loader be nullified for certain FormTypes
     */
    private function removeChoiceLoader($type, &$fieldOptions): void
    {
        if (in_array($type, [
            CountryType::class,
            CurrencyType::class,
            LanguageType::class,
            LocaleType::class,
            TimezoneType::class
        ])) {
            $fieldOptions['choice_loader'] = null;
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
