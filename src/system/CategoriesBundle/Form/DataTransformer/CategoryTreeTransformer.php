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

namespace Zikula\CategoriesBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Zikula\CategoriesBundle\Entity\Category;
use Zikula\CategoriesBundle\Repository\CategoryRepositoryInterface;

class CategoryTreeTransformer implements DataTransformerInterface
{
    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository)
    {
    }

    /**
     * Transform a CategoryEntity to its Id
     *
     * @param Category $category
     *
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
     *
     * @param mixed $categoryId
     *
     * @return Category|null
     */
    public function reverseTransform($categoryId): mixed
    {
        if (!$categoryId) {
            return null;
        }
        /** @var Category $category */
        $category = $this->categoryRepository->find($categoryId);
        if (null === $category) {
            throw new TransformationFailedException(sprintf('A category with number "%s" does not exist!', $categoryId));
        }

        return $category;
    }
}
