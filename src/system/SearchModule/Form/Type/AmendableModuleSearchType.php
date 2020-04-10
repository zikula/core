<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

/**
 * Class AmendableModuleSearchType
 *
 * This is a base form which is used with the SearchableInterface to allow providing modules to amend the
 * search form that is presented to the user. Each instance of this form is specific to the providing module.
 */
class AmendableModuleSearchType extends AbstractType
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    public function __construct(PermissionApiInterface $permissionApi)
    {
        $this->permissionApi = $permissionApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->permissionApi->hasPermission($builder->getName() . '::', '::', ACCESS_READ)) {
            return;
        }

        $builder->add('active', CheckboxType::class, [
            'label' => 'Active',
            'label_attr' => ['class' => 'switch-custom'],
            'required' => false,
            'data' => $options['active']
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikulasearchmodule_amendable_module_search';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'active' => true
        ]);
    }
}
