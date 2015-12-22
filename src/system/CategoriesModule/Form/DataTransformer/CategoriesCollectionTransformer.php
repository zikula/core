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

namespace Zikula\CategoriesModule\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Zikula\CategoriesModule\Entity\CategoryEntity;

/**
 * Class CategoriesCollectionTransformer
 * @package Zikula\CategoriesModule\Form\DataTransformer
 */
class CategoriesCollectionTransformer implements DataTransformerInterface
{
    private $entityCategoryClass;
    private $multiple;

    public function __construct(array $options)
    {
        $this->entityCategoryClass = $options['entityCategoryClass'];
        $this->multiple = isset($options['multiple']) ? $options['multiple'] : false;
    }

    public function reverseTransform($value)
    {
        $collection = new ArrayCollection();
        $class = $this->entityCategoryClass;

        foreach ($value as $regId => $categories) {
            $regId = (int) substr($regId, strpos($regId, '_') + 1);
            $subCollection = new ArrayCollection();
            if (!is_array($categories) && $categories instanceof CategoryEntity) {
                $categories = [$categories];
            } elseif (empty($categories)) {
                $categories = [];
            }
            foreach ($categories as $category) {
                $subCollection->add(new $class($regId, $category, null));
            }
            $collection->set($regId, $subCollection);
        }

        return $collection;
    }

    public function transform($value)
    {
        $data = [];
        if (empty($value)) {
            return $data;
        }

        foreach ($value as $categoryEntity) {
            $data['registry_' . $categoryEntity->getCategoryRegistryId()][] = $categoryEntity->getCategory();
        }

        return $data;
    }
}
