<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;

class RegexibleFormOptionsArrayType extends FormOptionsArrayType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('constraints', TextType::class, [
                'label' => $this->translator->__('Regex validation string constraint'),
                'required' => false,
            ]);
        $builder->get('constraints')
            ->addModelTransformer(new CallbackTransformer(
                function ($dataToDisplay) {
                    /** @var Regex $constraint */
                    $constraint = isset($dataToDisplay[0]) ? $dataToDisplay[0] : new Regex('/.*/');

                    return $constraint->pattern;
                },
                function ($dataToPersist) {
                    if (!$dataToPersist) {
                        $dataToPersist = new Regex('/.*/');
                    }

                    return [new Regex($dataToPersist)];
                }
            ))
        ;
    }
}
