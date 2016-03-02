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
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtensionModifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('displayname', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('url', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('description', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('defaults', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => __('Reload Defaults')
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaextensionsmodule_extensionmodify';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\ExtensionsModule\Entity\ExtensionEntity',
        ]);
    }
}
