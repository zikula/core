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

namespace Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\RegexConstraintTransformer;

class RegexibleFormOptionsArrayType extends FormOptionsArrayType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('constraints', TextType::class, [
            'label' => 'Regex validation string constraint',
            'required' => false
        ]);
        $builder->get('constraints')
            ->addModelTransformer(new RegexConstraintTransformer())
        ;
    }
}
