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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;

class LocaleType extends AbstractType
{
    public function __construct(private readonly LocaleApiInterface $localeApi)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'choices' => /** @Ignore */ $this->localeApi->getSupportedLocaleNames(),
            'label' => 'Locale',
            'required' => false,
            'placeholder' => 'All',
            'attr' => ['class' => 'locale-switcher-block'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'zikula_locale';
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
