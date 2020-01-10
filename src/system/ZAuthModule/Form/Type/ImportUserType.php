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

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class ImportUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'CSV file (Max. %maxSize%)',
                'label_translation_parameters' => [
                    '%maxSize%' => ini_get('post_max_size')
                ],
                'help' => 'The file must be utf8 encoded',
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
                            'application/txt'
                        ]
                    ])
                ]
            ])
            ->add('delimiter', ChoiceType::class, [
                'label' => 'CSV delimiter',
                'choices' => [
                    ',' => ',',
                    ';' => ';',
                    ':' => ':'
                ]
            ])
            ->add('upload', SubmitType::class, [
                'label' => 'Upload',
                'icon' => 'fa-upload',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_importuser';
    }
}
