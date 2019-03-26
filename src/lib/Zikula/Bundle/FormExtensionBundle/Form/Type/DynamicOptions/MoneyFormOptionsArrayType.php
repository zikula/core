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

namespace Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MoneyFormOptionsArrayType extends FormOptionsArrayType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('currency', TextType::class, [
                'empty_data' => 'EUR',
                'label' => $this->__('Currency'),
                'required' => false,
                'help' => $this->__('Any 3 letter ISO 4217 code. Default: EUR')
            ])
        ;
    }
}
