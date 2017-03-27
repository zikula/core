<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;

/**
 * Class CategoryTreeTransformer
 */
class CategoryTreeTransformer implements DataTransformerInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * CategoryTreeTransformer constructor.
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Transform a CategoryEntity to its Id
     *
     * @param CategoryEntity $category
     * @return string
     */
    public function transform($category)
    {
        if (null === $category) {
            return '';
        }

        return $category->getId();
    }

    /**
     * Transform a CategoryId to a CategoryEntity
     * @param mixed $categoryId
     * @return CategoryEntity|void
     */
    public function reverseTransform($categoryId)
    {
        if (!$categoryId) {
            return;
        }
        $category = $this->categoryRepository->find($categoryId);
        if (null === $category) {
            throw new TransformationFailedException(sprintf(
                'A category with number "%s" does not exist!',
                $categoryId
            ));
        }

        return $category;
    }
}
