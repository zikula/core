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

namespace Zikula\ExtensionsModule\ModuleInterface\Content\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\ExtensionsModule\ModuleInterface\Content\ContentTypeInterface;

/**
 * Abstract content type edit form type class.
 */
abstract class AbstractContentFormType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'zikula_contenttype_abstract';
    }

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
}
