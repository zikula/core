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

namespace Zikula\CategoriesModule\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;
use Zikula\CategoriesModule\Entity\CategoryEntity;

/**
 * Class CategoriesCollectionTransformer
 */
class CategoriesCollectionTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $entityCategoryClass;

    /**
     * @var bool
     */
    private $multiple;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(array $options)
    {
        $classParents = class_parents($options['entityCategoryClass']);
        if (!in_array(AbstractCategoryAssignment::class, $classParents, true)) {
            throw new InvalidConfigurationException("Option 'entityCategoryClass' must extend Zikula\\CategoriesModule\\Entity\\AbstractCategoryAssignment");
        }
        $this->entityCategoryClass = (string)$options['entityCategoryClass'];
        $this->multiple = (bool)($options['multiple'] ?? false);
        $this->entityManager = $options['em'];
    }

    public function reverseTransform($value)
    {
        $collection = new ArrayCollection();
        $class = $this->entityCategoryClass;

        foreach ($value as $regId => $categories) {
            $regId = (int)mb_substr($regId, mb_strpos($regId, '_') + 1);
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

        /** @var AbstractCategoryAssignment $categoryAssignmentEntity */
        foreach ($value as $categoryAssignmentEntity) {
            $registryKey = 'registry_' . $categoryAssignmentEntity->getCategoryRegistryId();
            $category = $categoryAssignmentEntity->getCategory();
            if (false !== mb_strpos(get_class($category), 'DoctrineProxy')) {
                $category = $this->entityManager->find(CategoryEntity::class, $category->getId());
            }

            if ($this->multiple) {
                $data[$registryKey][] = $category;
            } else {
                $data[$registryKey] = $category;
            }
        }

        return $data;
    }
}
