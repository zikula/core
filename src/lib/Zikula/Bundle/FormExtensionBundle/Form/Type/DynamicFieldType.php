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

namespace Zikula\Bundle\FormExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\FormExtensionBundle\Event\FormTypeChoiceEvent;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\ChoiceValuesTransformer;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\RegexConstraintTransformer;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\ChoiceFormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\DateTimeFormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\FormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\MoneyFormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\RegexibleFormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\FormTypesChoices;
use Zikula\ThemeModule\Api\ApiInterface\PageAssetApiInterface;
use Zikula\ThemeModule\Engine\Asset;

/**
 * Form type providing a dynamic selection of field type and field options.
 */
class DynamicFieldType extends AbstractType
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var PageAssetApiInterface
     */
    private $pageAssetApi;

    /**
     * @var Asset
     */
    private $assetHelper;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PageAssetApiInterface $pageAssetApi,
        Asset $assetHelper
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->pageAssetApi = $pageAssetApi;
        $this->assetHelper = $assetHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('formType', ChoiceType::class, [
            'label' => 'Field type',
            'choices' => $this->getChoices(),
            'placeholder' => 'Select'
        ]);

        $formModifier = function (FormInterface $form, $formType = null) use ($builder) {
            switch ($formType) {
                case ChoiceType::class:
                    $optionsType = ChoiceFormOptionsArrayType::class;
                    break;
                case DateType::class:
                case DateTimeType::class:
                case TimeType::class:
                case BirthdayType::class:
                    $optionsType = DateTimeFormOptionsArrayType::class;
                    break;
                case MoneyType::class:
                    $optionsType = MoneyFormOptionsArrayType::class;
                    break;
                case TextType::class:
                case TextareaType::class:
                    $optionsType = RegexibleFormOptionsArrayType::class;
                    break;
                default:
                    $optionsType = FormOptionsArrayType::class;
            }
            $formOptions = $builder->create('formOptions', $optionsType, [
                'label' => 'Field options',
                'auto_initialize' => false
            ]);
            if (ChoiceFormOptionsArrayType::class === $optionsType) {
                $formOptions->get('choices')->addModelTransformer(
                    new ChoiceValuesTransformer()
                );
            } elseif (RegexibleFormOptionsArrayType::class === $optionsType) {
                $formOptions->get('constraints')->addModelTransformer(
                    new RegexConstraintTransformer()
                );
            }
            $form->add($formOptions->getForm());
        };
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $formType = $data['formType'];
            $formModifier($event->getForm(), $formType);
        });
        $builder->get('formType')->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $event) use ($formModifier) {
            $formType = $event->getForm()->getData();
            $formModifier($event->getForm()->getParent(), $formType);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->pageAssetApi->add('javascript', $this->assetHelper->resolve('@ZikulaFormExtensionBundle:js/ZikulaFormExtensionBundle.DynamicField.Edit.js'));
    }

    public function getBlockPrefix()
    {
        return 'zikulaformextensionbundle_dynamicfield';
    }

    private function getChoices(): FormTypesChoices
    {
        $choices = new FormTypesChoices([
            'Text fields' => [
                'Text' => TextType::class,
                'Textarea' => TextareaType::class,
                'Email' => EmailType::class,
                'Integer' => IntegerType::class,
                'Money' => MoneyType::class,
                'Number' => NumberType::class,
                'Password' => PasswordType::class,
                'Percent' => PercentType::class,
                'Url' => UrlType::class,
                'Range' => RangeType::class,
            ],
            'Choice fields' => [
                'Choice' => ChoiceType::class,
                'Country' => CountryType::class,
                'Language' => LanguageType::class,
                'Locale' => LocaleType::class,
                'Timezone' => TimezoneType::class,
                'Currency' => CurrencyType::class,
            ],
            'Date and time fields' => [
                'Date' => DateType::class,
                'DateTime' => DateTimeType::class,
                'Time' => TimeType::class,
                'Birthday' => BirthdayType::class,
            ],
            'Other fields' => [
                'Checkbox' => CheckboxType::class,
                'Radio' => RadioType::class,
            ]
        ]);

        $event = new FormTypeChoiceEvent($choices);
        $this->eventDispatcher->dispatch($event, FormTypeChoiceEvent::NAME);

        return $choices;
    }
}
