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

namespace Zikula\ThemeBundle\Form;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FiltersFormType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactory as OriginalFormFactory;
use Symfony\Component\Form\FormInterface;

/**
 * Define separate validation groups for filter forms to avoid default validation errors.
 *
 * @see https://github.com/EasyCorp/EasyAdminBundle/issues/4737
 * @see https://github.com/EasyCorp/EasyAdminBundle/issues/4842
 */
class FormFactory extends OriginalFormFactory
{
    public function createNamed(string $name, string $type = FormType::class, mixed $data = null, array $options = []): FormInterface
    {
        if ('filters' === $name && FiltersFormType::class === $type) {
            $options = array_merge($options, [
                'validation_groups' => ['admin_filter'],
            ]);
        }

        return parent::createNamed($name, $type, $data, $options);
    }
}
