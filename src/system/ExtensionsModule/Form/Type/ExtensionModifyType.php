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
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('defaults', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Reload Defaults'),
                'icon' => 'fa-refresh',
                'attr' => [
                    'class' => 'btn btn-warning'
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
