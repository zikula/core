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

namespace Zikula\CategoriesModule\Tests\Fixtures;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\CategoriesModule\Form\Type\CategoriesType;

class CategorizableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('categoryAssignments', CategoriesType::class, [
            'em' => $options['em'],
            'required' => $options['required'],
            'multiple' => $options['multiple'],
            'attr' => $options['attr'],
            'expanded' => $options['expanded'],
            'direct' => $options['direct'],
            'module' => 'AcmeFooModule',
            'entity' => 'CategorizableEntity',
            'entityCategoryClass' => CategoryAssignmentEntity::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikulacategoriesmodule_test_categorizable';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'em' => null,
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'attr' => [],
            'direct' => true,
            'data_class' => CategorizableEntity::class
        ]);
    }
}
