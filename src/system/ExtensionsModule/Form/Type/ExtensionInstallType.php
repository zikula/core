<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ExtensionInstallType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dependencies', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'entry_type' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
            ])
            ->add('install', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Install'),
                'icon' => 'fa-plus',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaextensionsmodule_extensioninstall';
    }
}
