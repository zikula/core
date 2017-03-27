<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Tests\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Form\DataTransformer\CategoriesCollectionTransformer;
use Zikula\CategoriesModule\Tests\Fixtures\CategoryAssignmentEntity;

class CategoriesCollectionTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransform()
    {
        $this->assertEquals(1, 1);
    }

    public function testReverseTransformEmptyArray()
    {
        $transformer = $this->getTransformer();
        $this->assertEquals(new ArrayCollection(), $transformer->reverseTransform([]));
    }

    public function testTransformEmptyArray()
    {
        $transformer = $this->getTransformer();
        $this->assertEquals([], $transformer->transform([]));
    }

    public function testTransformSingleAssociation()
    {
        $transformer = $this->getTransformer();

        $category = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $categoryAssignment = $this->generateCategoryAssignment($category, 1);

        $expected = ['registry_1' => $category];
        $this->assertEquals($expected, $transformer->transform([$categoryAssignment]));
    }

    public function testReverseTransformSingleAssociation()
    {
        $transformer = $this->getTransformer();
        $subCollection = new ArrayCollection();

        $category = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $subCollection->add(new CategoryAssignmentEntity(1, $category, null));

        $expected = new ArrayCollection();
        $expected->set(1, $subCollection);
        $this->assertEquals($expected, $transformer->reverseTransform(['registry_1' => $category]));
    }

    public function testTransformMultipleAssociation()
    {
        $transformer = $this->getTransformer(true);

        $categoryA = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $categoryAssignmentA = $this->generateCategoryAssignment($categoryA, 1);
        $categoryB = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $categoryAssignmentB = $this->generateCategoryAssignment($categoryB, 1);

        $expected = ['registry_1' => [$categoryA, $categoryB]];
        $this->assertEquals($expected, $transformer->transform([$categoryAssignmentA, $categoryAssignmentB]));
    }

    public function testReverseTransformMultipleAssociation()
    {
        $transformer = $this->getTransformer(true);
        $subCollection = new ArrayCollection();

        $categoryA = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $subCollection->add(new CategoryAssignmentEntity(1, $categoryA, null));

        $categoryB = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $subCollection->add(new CategoryAssignmentEntity(1, $categoryB, null));

        $expected = new ArrayCollection();
        $expected->set(1, $subCollection);
        $this->assertEquals($expected, $transformer->reverseTransform(['registry_1' => [$categoryA, $categoryB]]));
    }

    public function testTransformMultipleRegistriesAndAssociations()
    {
        $transformer = $this->getTransformer(true);

        $categoryA = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $categoryAssignmentA = $this->generateCategoryAssignment($categoryA, 1);
        $categoryB = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $categoryAssignmentB = $this->generateCategoryAssignment($categoryB, 1);
        $categoryC = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $categoryAssignmentC = $this->generateCategoryAssignment($categoryC, 2);
        $categoryD = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $categoryAssignmentD = $this->generateCategoryAssignment($categoryD, 2);

        $expected = [
            'registry_1' => [$categoryA, $categoryB],
            'registry_2' => [$categoryC, $categoryD],
        ];
        $this->assertEquals($expected, $transformer->transform([$categoryAssignmentA, $categoryAssignmentB, $categoryAssignmentC, $categoryAssignmentD]));
    }

    public function testReverseTransformMultipleRegistriesAndAssociations()
    {
        $transformer = $this->getTransformer(true);
        $subCollectionA = new ArrayCollection();
        $subCollectionB = new ArrayCollection();

        $categoryA = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $subCollectionA->add(new CategoryAssignmentEntity(1, $categoryA, null));

        $categoryB = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $subCollectionA->add(new CategoryAssignmentEntity(1, $categoryB, null));

        $categoryC = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $subCollectionB->add(new CategoryAssignmentEntity(2, $categoryC, null));

        $categoryD = $this->getMockBuilder(CategoryEntity::class)->getMock();
        $subCollectionB->add(new CategoryAssignmentEntity(2, $categoryD, null));

        $expected = new ArrayCollection();
        $expected->set(1, $subCollectionA);
        $expected->set(2, $subCollectionB);
        $this->assertEquals($expected, $transformer->reverseTransform([
            'registry_1' => [$categoryA, $categoryB],
            'registry_2' => [$categoryC, $categoryD]
        ]));
    }

    protected function getTransformer($multiple = false)
    {
        $options = [
            'entityCategoryClass' => CategoryAssignmentEntity::class,
            'multiple' => $multiple
        ];

        return new CategoriesCollectionTransformer($options);
    }

    protected function generateCategoryAssignment($category, $registryId)
    {
        $categoryAssignment = $this->getMockBuilder(AbstractCategoryAssignment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryAssignment->method('getCategory')->willReturn($category);
        $categoryAssignment->method('getCategoryRegistryId')->willReturn($registryId);

        return $categoryAssignment;
    }
}
