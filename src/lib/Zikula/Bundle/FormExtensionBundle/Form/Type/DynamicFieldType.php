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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
use Zikula\Bundle\FormExtensionBundle\Event\FormTypeChoiceEvent;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\RegexConstraintTransformer;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\ChoiceValuesTransformer;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\ChoiceFormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\DateTimeFormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\FormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\MoneyFormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions\RegexibleFormOptionsArrayType;
use Zikula\Bundle\FormExtensionBundle\FormTypesChoices;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
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
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var PageAssetApiInterface
     */
    private $pageAssetApi;

    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * DynamicFieldType constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param LocaleApiInterface $localeApi
     * @param TranslatorInterface $translator
     * @param PageAssetApiInterface $pageAssetApi
     * @param Asset $assetHelper
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        LocaleApiInterface $localeApi,
        TranslatorInterface $translator,
        PageAssetApiInterface $pageAssetApi,
        Asset $assetHelper
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->localeApi = $localeApi;
        $this->setTranslator($translator);
        $this->pageAssetApi = $pageAssetApi;
        $this->assetHelper = $assetHelper;
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
        $builder->add('formType', ChoiceType::class, [
            'label' => $this->__('Field type'),
            'choices' => $this->getChoices(),
            'placeholder' => $this->__('Select')
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
                'label' => $this->__('Field options'),
                'auto_initialize' => false
            ]);
            if (ChoiceFormOptionsArrayType::class == $optionsType) {
                $formOptions->get('choices')->addModelTransformer(
                    new ChoiceValuesTransformer()
                );
            } elseif (RegexibleFormOptionsArrayType::class == $optionsType) {
                $formOptions->get('constraints')->addModelTransformer(
                    new RegexConstraintTransformer()
                );
            }
            $form->add($formOptions->getForm());
        };
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $formType = $data['formType'];
            $formModifier($event->getForm(), $formType);
        });
        $builder->get('formType')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $formType = $event->getForm()->getData();
            $formModifier($event->getForm()->getParent(), $formType);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->pageAssetApi->add('javascript', $this->assetHelper->resolve('@ZikulaFormExtensionBundle:js/ZikulaFormExtensionBundle.DynamicField.Edit.js'));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulaformextensionbundle_dynamicfield';
    }

    private function getChoices()
    {
        $choices = new FormTypesChoices([
            $this->__('Text Fields') => [
                $this->__('Text') => TextType::class,
                $this->__('Textarea') => TextareaType::class,
                $this->__('Email') => EmailType::class,
                $this->__('Integer') => IntegerType::class,
                $this->__('Money') => MoneyType::class,
                $this->__('Number') => NumberType::class,
                $this->__('Password') => PasswordType::class,
                $this->__('Percent') => PercentType::class,
                $this->__('Url') => UrlType::class,
                $this->__('Range') => RangeType::class,
            ],
            $this->__('Choice Fields') => [
                $this->__('Choice') => ChoiceType::class,
                $this->__('Country') => CountryType::class,
                $this->__('Language') => LanguageType::class,
                $this->__('Locale') => LocaleType::class,
                $this->__('Timezone') => TimezoneType::class,
                $this->__('Currency') => CurrencyType::class,
            ],
            $this->__('Date and Time Fields') => [
                $this->__('Date') => DateType::class,
                $this->__('DateTime') => DateTimeType::class,
                $this->__('Time') => TimeType::class,
                $this->__('Birthday') => BirthdayType::class,
            ],
            $this->__('Other Fields') => [
                $this->__('Checkbox') => CheckboxType::class,
                $this->__('Radio') => RadioType::class,
            ],
        ]);

        $event = new FormTypeChoiceEvent($choices);
        $this->eventDispatcher->dispatch(FormTypeChoiceEvent::NAME, $event);

        return $choices;
    }
}
