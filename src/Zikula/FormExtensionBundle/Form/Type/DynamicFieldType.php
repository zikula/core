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
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\WeekType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
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
    use TranslatorTrait;

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
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        PageAssetApiInterface $pageAssetApi,
        Asset $assetHelper
    ) {
        $this->setTranslator($translator);
        $this->eventDispatcher = $eventDispatcher;
        $this->pageAssetApi = $pageAssetApi;
        $this->assetHelper = $assetHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('formType', ChoiceType::class, [
            'label' => 'Field type',
            'choices' => /** @Ignore */$this->getChoices(),
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
            $this->trans('Text fields') => [
                $this->trans('Text') => TextType::class,
                $this->trans('Textarea') => TextareaType::class,
                $this->trans('Email') => EmailType::class,
                $this->trans('Icon') => IconType::class,
                $this->trans('Integer') => IntegerType::class,
                $this->trans('Money') => MoneyType::class,
                $this->trans('Number') => NumberType::class,
                $this->trans('Password') => PasswordType::class,
                $this->trans('Percent') => PercentType::class,
                $this->trans('Phone number') => TelType::class,
                $this->trans('Url') => UrlType::class,
                $this->trans('Range') => RangeType::class,
                $this->trans('Week number') => WeekType::class,
            ],
            $this->trans('Choice fields') => [
                $this->trans('Choice') => ChoiceType::class,
                $this->trans('Country') => CountryType::class,
                $this->trans('Language') => LanguageType::class,
                $this->trans('Locale') => LocaleType::class,
                $this->trans('Timezone') => TimezoneType::class,
                $this->trans('Currency') => CurrencyType::class,
            ],
            $this->trans('Date and time fields') => [
                $this->trans('Date') => DateType::class,
                $this->trans('DateTime') => DateTimeType::class,
                $this->trans('Time') => TimeType::class,
                $this->trans('Birthday') => BirthdayType::class,
            ],
            $this->trans('Other fields') => [
                $this->trans('Checkbox') => CheckboxType::class,
                $this->trans('Radio') => RadioType::class,
            ]
        ]);

        $event = new FormTypeChoiceEvent($choices);
        $this->eventDispatcher->dispatch($event, FormTypeChoiceEvent::NAME);

        return $choices;
    }
}
