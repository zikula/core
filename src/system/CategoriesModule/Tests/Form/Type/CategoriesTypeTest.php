<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Tests\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Gedmo\Tree\TreeListener;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Zikula\CategoriesModule\Api\CategoryRegistryApi;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\CategoriesModule\Tests\Fixtures\CategorizableEntity;
use Zikula\CategoriesModule\Tests\Fixtures\CategoryAssignmentEntity;

/**
 * Class CategoriesTypeTest
 * @see http://symfony.com/doc/2.8/form/unit_testing.html
 */
class CategoriesTypeTest extends TypeTestCase
{
    const CATEGORY_ENTITY = 'Zikula\CategoriesModule\Entity\CategoryEntity';
    const CATEGORIZABLE_ENTITY = 'Zikula\CategoriesModule\Tests\Fixtures\CategorizableEntity';
    const CATEGORY_ASSIGNMENT_ENTITY = 'Zikula\CategoriesModule\Tests\Fixtures\CategoryAssignmentEntity';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private $emRegistry;

    /**
     * @var CategoryRegistryApi
     */
    private $categoryRegistryApi;

    protected function setUp()
    {
        $this->em = DoctrineTestHelper::createTestEntityManager();
        $this->emRegistry = $this->createRegistryMock('default', $this->em);
        $this->categoryRegistryApi = $this->createCategoryRegistryApiMock();
        $this->em->getEventManager()->addEventSubscriber(new TreeListener());

        parent::setUp();

        $schemaTool = new SchemaTool($this->em);
        $classes = [
            $this->em->getClassMetadata(self::CATEGORY_ENTITY),
            $this->em->getClassMetadata(self::CATEGORIZABLE_ENTITY),
            $this->em->getClassMetadata(self::CATEGORY_ASSIGNMENT_ENTITY),
        ];

        try {
            $schemaTool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (\Exception $e) {
        }
        $this->generateCategories();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em = null;
        $this->emRegistry = null;
    }

    protected function getExtensions()
    {
        $type = new CategoriesType($this->categoryRegistryApi);

        return [
            new PreloadedExtension([$type], []),
            new DoctrineOrmExtension($this->emRegistry),
        ];
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function testClassOptionIsRequired()
    {
        $this->factory->createNamed('name', 'Zikula\CategoriesModule\Form\Type\CategoriesType');
    }

    public function testSubmitValidData()
    {
        $formData = [
            'categoryAssignments' => ['registry_1' => '2'],
        ];

        $form = $this->factory->create('Zikula\CategoriesModule\Tests\Fixtures\CategorizableType', new CategorizableEntity(), ['em' => $this->em]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', 2);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $expectedObject));
        $expectedObject->setCategoryAssignments($categoryAssignments);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedObject, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testSubmitMultipleValidData()
    {
        $formData = [
            'categoryAssignments' => ['registry_1' => ['2', '3']],
        ];

        $form = $this->factory->create('Zikula\CategoriesModule\Tests\Fixtures\CategorizableType', new CategorizableEntity(), [
            'em' => $this->em,
            'multiple' => true
        ]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        foreach ([2, 3] as $id) {
            $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', $id);
            $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $expectedObject));
        }
        $expectedObject->setCategoryAssignments($categoryAssignments);

        // submit the data to the form directly
        $form->submit($formData);
        $this->assertEquals($expectedObject, $form->getData());
    }

    public function testSubmitWithExistingData()
    {
        $formData = [
            'categoryAssignments' => ['registry_1' => '2'],
        ];
        $existingObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', 1);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create('Zikula\CategoriesModule\Tests\Fixtures\CategorizableType', $existingObject, ['em' => $this->em]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', 2);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $expectedObject));
        $expectedObject->setCategoryAssignments($categoryAssignments);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedObject, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testSubmitMultipleWithExistingData()
    {
        $formData = [
            'categoryAssignments' => ['registry_1' => ['2', '3']],
        ];
        $existingObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', 1);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create('Zikula\CategoriesModule\Tests\Fixtures\CategorizableType', new CategorizableEntity(), [
            'em' => $this->em,
            'multiple' => true
        ]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        foreach ([2, 3] as $id) {
            $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', $id);
            $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $expectedObject));
        }
        $expectedObject->setCategoryAssignments($categoryAssignments);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedObject, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testSubmitSingleWithMultipleExistingData()
    {
        $formData = [
            'categoryAssignments' => ['registry_1' => '2'],
        ];
        $existingObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        foreach ([2, 3] as $id) {
            $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', $id);
            $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        }
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create('Zikula\CategoriesModule\Tests\Fixtures\CategorizableType', $existingObject, ['em' => $this->em]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', 2);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $expectedObject));
        $expectedObject->setCategoryAssignments($categoryAssignments);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedObject, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testSubmitEmptyWithExistingData()
    {
        $formData = [
            'categoryAssignments' => [],
        ];
        $existingObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', 1);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create('Zikula\CategoriesModule\Tests\Fixtures\CategorizableType', $existingObject, ['em' => $this->em]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $expectedObject->setCategoryAssignments($categoryAssignments);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedObject, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testSubmitEmptyMultipleWithExistingData()
    {
        $formData = [
            'categoryAssignments' => [],
        ];
        $existingObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference('Zikula\CategoriesModule\Entity\CategoryEntity', 1);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create('Zikula\CategoriesModule\Tests\Fixtures\CategorizableType', new CategorizableEntity(), [
            'em' => $this->em,
            'multiple' => true
        ]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $expectedObject->setCategoryAssignments($categoryAssignments);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedObject, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    protected function createRegistryMock($name, $em)
    {
        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')->getMock();
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($name))
            ->will($this->returnValue($em));

        return $registry;
    }

    protected function createCategoryRegistryApiMock()
    {
        $categoryRegistryApi = $this->getMockBuilder('Zikula\CategoriesModule\Api\CategoryRegistryApi')
            ->disableOriginalConstructor()
            ->getMock();
        $categoryRegistryApi->method('getModuleCategoryIds')
            ->willReturn([1 => 1]); // $registryId => $categoryId

        return $categoryRegistryApi;
    }

    protected function generateCategories()
    {
        $now = new \DateTime();

        $root = new CategoryEntity();
        $root->setId(1);
        $root->setName('root');
        $root->setDisplay_name(['en' => 'root']);
        $root->setCr_date($now);
        $root->setLu_date($now);
        $this->em->getRepository('Zikula\CategoriesModule\Entity\CategoryEntity')->persistAsFirstChild($root);

        $a = new CategoryEntity();
        $a->setId(2);
        $a->setParent($root);
        $a->setName('a');
        $a->setDisplay_name(['en' => 'a']);
        $a->setCr_date($now);
        $a->setLu_date($now);
        $this->em->getRepository('Zikula\CategoriesModule\Entity\CategoryEntity')->persistAsFirstChildOf($a, $root);

        $b = new CategoryEntity();
        $b->setId(3);
        $b->setParent($root);
        $b->setName('b');
        $b->setDisplay_name(['en' => 'b']);
        $b->setCr_date($now);
        $b->setLu_date($now);
        $this->em->getRepository('Zikula\CategoriesModule\Entity\CategoryEntity')->persistAsLastChildOf($b, $root);

        $this->em->flush();
    }
}
