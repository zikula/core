<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Tests\Fixtures;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\CategoriesModule\Api\CategoryApi;

class CategorizableType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('categoryAssignments', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
                'em' => $options['em'],
                'required' => $options['required'],
                'multiple' => $options['multiple'],
                'attr' => $options['attr'],
                'module' => 'AcmeFooModule',
                'entity' => 'CategorizableEntity',
                'entityCategoryClass' => 'Zikula\CategoriesModule\Tests\Fixtures\CategoryAssignmentEntity',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulacategoriesmodule_test_categorizable';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'em' => null,
            'required' => true,
            'multiple' => false,
            'attr' => [],
            'data_class' => 'Zikula\CategoriesModule\Tests\Fixtures\CategorizableEntity'
        ]);
    }
}
