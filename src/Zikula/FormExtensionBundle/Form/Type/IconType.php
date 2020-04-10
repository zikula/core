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
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Icon form type.
 */
class IconType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'zikula_icon';
    }

    public function getParent()
    {
        return TextType::class;
    }
}
