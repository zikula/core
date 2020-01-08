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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

/**
 * Locale form type.
 */
class LocaleType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var LocaleApiInterface
     */
    protected $localeApi;

    public function __construct(TranslatorInterface $translator, LocaleApiInterface $localeApi)
    {
        $this->setTranslator($translator);
        $this->localeApi = $localeApi;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->localeApi->getSupportedLocaleNames(),
            'label' => 'Locale',
            'required' => false,
            'placeholder' => $this->trans('All'),
            'attr' => ['class' => 'locale-switcher-block']
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikula_locale';
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
