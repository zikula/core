<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
            ->add('install', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', ['label' => 'Install'])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', ['label' => 'Cancel'])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaextensionsmodule_extensioninstall';
    }
}
