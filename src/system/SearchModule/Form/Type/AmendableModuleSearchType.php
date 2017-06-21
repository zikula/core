<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\PermissionsModule\PermissionAlways;

/**
 * Class AmendableModuleSearchType
 *
 * This is a base form which is used with the SearchableInterface to allow providing modules to amend the
 * search form that is presented to the user. Each instance of this form is specific to the providing module.
 */
class AmendableModuleSearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['permissionApi']->hasPermission($builder->getName() . '::', '::', ACCESS_READ)) {
            $builder
                ->add('active', CheckboxType::class, [
                    'label' => $options['translator']->__('Active'),
                    'label_attr' => ['class' => 'checkbox-inline'],
                    'required' => false,
                    'data' => $options['active']
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulasearchmodule_amendable_module_search';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => new IdentityTranslator(),
            'active' => true,
            'permissionApi' => new PermissionAlways()
        ]);
    }
}
