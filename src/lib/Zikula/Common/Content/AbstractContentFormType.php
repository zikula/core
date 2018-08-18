<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\Content;

use Symfony\Component\Form\AbstractType as AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Abstract content type edit form type class.
 */
abstract class AbstractContentFormType extends AbstractType
{
    use TranslatorTrait;

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikula_contenttype_abstract';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'context' => ContentTypeInterface::CONTEXT_EDIT
            ])
            ->setAllowedTypes('context', 'string')
            ->setAllowedValues('context', [ContentTypeInterface::CONTEXT_EDIT, ContentTypeInterface::CONTEXT_TRANSLATION])
        ;
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
