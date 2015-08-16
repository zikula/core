<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\CategoriesModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Zikula\CategoriesModule\Form\DataTransformer\CategoryDisplayTransformer;
use Zikula\CategoriesModule\Form\DataTransformer\CategoryModelTransformer;

/**
 * Class CategoryType
 * @package Zikula\CategoriesModule\Form\Type
 */
class CategoryType extends AbstractType
{
    /**
     * The category ID of the root/parent category
     * @var integer|null
     */
    private $parentId;
    /**
     * The registry ID (table id) of the registry this relation is connected to
     * @var integer|null
     */
    private $registryId;
    /**
     * language code (e.g. `de`)
     * @var string
     */
    private $lang;

    function __construct($registryId = null, $parentId = null)
    {
        if (!empty($parentId)) {
            $this->parentId = $parentId;
        } else {
            $parentIdCategory = \CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Global');
            $this->parentId = $parentIdCategory['id'];
        }
        $this->registryId = $registryId;
        $this->lang = \ZLanguage::getLanguageCode();
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'ZikulaCategoriesModule:CategoryEntity',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->where('c.parent = :parent')
                    ->setParameter('parent', $this->parentId)
                    ->orderBy('c.name', 'ASC');
            },
            'property' => "display_name[$this->lang]",
            'multiple' => false,
            'required' => false,
            'placeholder' => __('Choose a category'),
            'registry_id' => $this->registryId,
            'parent_id' => $this->parentId
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['registry_id'] = $options['registry_id'];
        $view->vars['parent_id'] = $options['parent_id'];
        // replace the full name with an array-type name e.g. `[]`
        $fullName = str_replace($view->vars['name'], $view->vars['name'] . '][' . $options['registry_id'], $view->vars['full_name']);
        $view->vars['full_name'] = $fullName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new CategoryDisplayTransformer($this->registryId))
            ->addModelTransformer(new CategoryModelTransformer($this->registryId));
    }

    public function getName()
    {
        return 'zikulacategoriesmodule_category';
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getRegistryId()
    {
        return $this->registryId;
    }

    public function getParentId()
    {
        return $this->parentId;
    }
}