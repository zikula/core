<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImportUserType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'Symfony\Component\Form\Extension\Core\Type\FileType', [
                'label' => $options['translator']->__f('CSV file (Max. %sub%)', ['%sub%' => ini_get('post_max_size')]),
                'help' => $options['translator']->__('The file must be utf8 encoded'),
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'text/comma-separated-values',
                            'application/excel',
                            'application/vnd.ms-excel',
                            'application/vnd.msexcel',
                            'text/anytext',
                            'application/octet-stream',
                            'application/txt',
                        ]
                    ])
                ]
            ])
            ->add('delimiter', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('CSV delimiter'),
                'choices' => [
                    ',' => ',',
                    ';' => ';',
                    ':' => ':',
                ],
                'choices_as_values' => true
            ])
            ->add('upload', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Upload'),
                'icon' => 'fa-upload',
                'attr' => ['class' => 'btn btn-success'],
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    /**
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_importuser';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}
