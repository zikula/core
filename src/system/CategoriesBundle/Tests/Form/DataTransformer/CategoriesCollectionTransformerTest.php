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

namespace Zikula\CategoriesBundle\Tests\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\DataTransformerInterface;
use Zikula\CategoriesBundle\Entity\AbstractCategoryAssignment;
use Zikula\CategoriesBundle\Entity\Category;
use Zikula\CategoriesBundle\Form\DataTransformer\CategoriesCollectionTransformer;
use Zikula\CategoriesBundle\Tests\Fixtures\CategoryAssignment;

class CategoriesCollectionTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $this->assertEquals(1, 1);
    }

    public function testReverseTransformEmptyArray(): void
    {
        $transformer = $this->getTransformer();
        $this->assertEquals(new ArrayCollection(), $transformer->reverseTransform([]));
    }

    public function testTransformEmptyArray(): void
    {
        $transformer = $this->getTransformer();
        $this->assertEquals([], $transformer->transform([]));
    }

    public function testTransformSingleAssociation(): void
    {
        $transformer = $this->getTransformer();

        $category = $this->getMockBuilder(Category::class)->getMock();
        $categoryAssignment = $this->generateCategoryAssignment($category, 1);

        $expected = ['registry_1' => $category];
        $this->assertEquals($expected, $transformer->transform([$categoryAssignment]));
    }

    public function testReverseTransformSingleAssociation(): void
    {
        $transformer = $this->getTransformer();
        $subCollection = new ArrayCollection();

        $category = $this->getMockBuilder(Category::class)->getMock();
        $subCollection->add(new CategoryAssignment(1, $category, null));

        $expected = new ArrayCollection();
        $expected->set(1, $subCollection);
        $this->assertEquals($expected, $transformer->reverseTransform(['registry_1' => $category]));
    }

    public function testTransformMultipleAssociation(): void
    {
        $transformer = $this->getTransformer(true);

        $categoryA = $this->getMockBuilder(Category::class)->getMock();
        $categoryAssignmentA = $this->generateCategoryAssignment($categoryA, 1);
        $categoryB = $this->getMockBuilder(Category::class)->getMock();
        $categoryAssignmentB = $this->generateCategoryAssignment($categoryB, 1);

        $expected = ['registry_1' => [$categoryA, $categoryB]];
        $this->assertEquals($expected, $transformer->transform([$categoryAssignmentA, $categoryAssignmentB]));
    }

    public function testReverseTransformMultipleAssociation(): void
    {
        $transformer = $this->getTransformer(true);
        $subCollection = new ArrayCollection();

        $categoryA = $this->getMockBuilder(Category::class)->getMock();
        $subCollection->add(new CategoryAssignment(1, $categoryA, null));

        $categoryB = $this->getMockBuilder(Category::class)->getMock();
        $subCollection->add(new CategoryAssignment(1, $categoryB, null));

        $expected = new ArrayCollection();
        $expected->set(1, $subCollection);
        $this->assertEquals($expected, $transformer->reverseTransform(['registry_1' => [$categoryA, $categoryB]]));
    }

    public function testTransformMultipleRegistriesAndAssociations(): void
    {
        $transformer = $this->getTransformer(true);

        $categoryA = $this->getMockBuilder(Category::class)->getMock();
        $categoryAssignmentA = $this->generateCategoryAssignment($categoryA, 1);
        $categoryB = $this->getMockBuilder(Category::class)->getMock();
        $categoryAssignmentB = $this->generateCategoryAssignment($categoryB, 1);
        $categoryC = $this->getMockBuilder(Category::class)->getMock();
        $categoryAssignmentC = $this->generateCategoryAssignment($categoryC, 2);
        $categoryD = $this->getMockBuilder(Category::class)->getMock();
        $categoryAssignmentD = $this->generateCategoryAssignment($categoryD, 2);

        $expected = [
            'registry_1' => [$categoryA, $categoryB],
            'registry_2' => [$categoryC, $categoryD],
        ];
        $this->assertEquals($expected, $transformer->transform([$categoryAssignmentA, $categoryAssignmentB, $categoryAssignmentC, $categoryAssignmentD]));
    }

    public function testReverseTransformMultipleRegistriesAndAssociations(): void
    {
        $transformer = $this->getTransformer(true);
        $subCollectionA = new ArrayCollection();
        $subCollectionB = new ArrayCollection();

        $categoryA = $this->getMockBuilder(Category::class)->getMock();
        $subCollectionA->add(new CategoryAssignment(1, $categoryA, null));

        $categoryB = $this->getMockBuilder(Category::class)->getMock();
        $subCollectionA->add(new CategoryAssignment(1, $categoryB, null));

        $categoryC = $this->getMockBuilder(Category::class)->getMock();
        $subCollectionB->add(new CategoryAssignment(2, $categoryC, null));

        $categoryD = $this->getMockBuilder(Category::class)->getMock();
        $subCollectionB->add(new CategoryAssignment(2, $categoryD, null));

        $expected = new ArrayCollection();
        $expected->set(1, $subCollectionA);
        $expected->set(2, $subCollectionB);
        $this->assertEquals($expected, $transformer->reverseTransform([
            'registry_1' => [$categoryA, $categoryB],
            'registry_2' => [$categoryC, $categoryD]
        ]));
    }

    protected function getTransformer(bool $multiple = false): DataTransformerInterface
    {
        $options = [
            'entityCategoryClass' => CategoryAssignment::class,
            'multiple' => $multiple,
            'em' => DoctrineTestHelper::createTestEntityManager()
        ];

        return new CategoriesCollectionTransformer($options);
    }

    protected function generateCategoryAssignment($category, $registryId): AbstractCategoryAssignment
    {
        $categoryAssignment = $this->getMockForAbstractClass(
            AbstractCategoryAssignment::class,
            [],
            '',
            false,
            false,
            true,
            ['getCategory', 'getCategoryRegistryId']
        );
        $categoryAssignment->method('getCategory')->willReturn($category);
        $categoryAssignment->method('getCategoryRegistryId')->willReturn($registryId);

        return $categoryAssignment;
    }
}
