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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\CategoriesModule\Tests\Fixtures\CategorizableEntity;
use Zikula\CategoriesModule\Tests\Fixtures\CategorizableType;
use Zikula\CategoriesModule\Tests\Fixtures\CategoryAssignmentEntity;

/**
 * Class CategoriesTypeTest
 * @see http://symfony.com/doc/2.8/form/unit_testing.html
 */
class CategoriesTypeTest extends TypeTestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private $emRegistry;

    protected function setUp()
    {
        $this->em = DoctrineTestHelper::createTestEntityManager();
        $this->emRegistry = $this->createRegistryMock('default', $this->em);
        $this->em->getEventManager()->addEventSubscriber(new TreeListener());

        parent::setUp();

        $schemaTool = new SchemaTool($this->em);
        $classes = [
            $this->em->getClassMetadata(CategoryEntity::class),
            $this->em->getClassMetadata(CategoryRegistryEntity::class),
            $this->em->getClassMetadata(CategorizableEntity::class),
            $this->em->getClassMetadata(CategoryAssignmentEntity::class),
        ];

        try {
            $schemaTool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (\Exception $e) {
        }
        $now = new \DateTime();
        $this->generateCategories($now);
        $this->generateCategoryRegistry($now);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em = null;
        $this->emRegistry = null;
    }

    protected function getExtensions()
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'foo' => 'bar'
        ]));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $type = new CategoriesType($this->em->getRepository(CategoryRegistryEntity::class), $requestStack);

        return [
            new PreloadedExtension([$type], []),
            new DoctrineOrmExtension($this->emRegistry),
        ];
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testClassOptionIsRequired()
    {
        $this->factory->createNamed('name', CategoriesType::class);
    }

    public function testSubmitValidData()
    {
        $formData = [
            'categoryAssignments' => ['registry_1' => '2'],
        ];

        $form = $this->factory->create(CategorizableType::class, new CategorizableEntity(), ['em' => $this->em]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference(CategoryEntity::class, 2);
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

        $form = $this->factory->create(CategorizableType::class, new CategorizableEntity(), [
            'em' => $this->em,
            'multiple' => true
        ]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        foreach ([2, 3] as $id) {
            $assignedCategory = $this->em->getReference(CategoryEntity::class, $id);
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
        $assignedCategory = $this->em->getReference(CategoryEntity::class, 1);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create(CategorizableType::class, $existingObject, ['em' => $this->em]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference(CategoryEntity::class, 2);
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
        $assignedCategory = $this->em->getReference(CategoryEntity::class, 1);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create(CategorizableType::class, new CategorizableEntity(), [
            'em' => $this->em,
            'multiple' => true
        ]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        foreach ([2, 3] as $id) {
            $assignedCategory = $this->em->getReference(CategoryEntity::class, $id);
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
            $assignedCategory = $this->em->getReference(CategoryEntity::class, $id);
            $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        }
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create(CategorizableType::class, $existingObject, ['em' => $this->em]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference(CategoryEntity::class, 2);
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
        $assignedCategory = $this->em->getReference(CategoryEntity::class, 1);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create(CategorizableType::class, $existingObject, ['em' => $this->em]);

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
        $assignedCategory = $this->em->getReference(CategoryEntity::class, 1);
        $categoryAssignments->add(new CategoryAssignmentEntity(1, $assignedCategory, $existingObject));
        $existingObject->setCategoryAssignments($categoryAssignments);

        $form = $this->factory->create(CategorizableType::class, new CategorizableEntity(), [
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

    /**
     * @deprecated the 'includeGrandChild' option is deprecated - remove this test when removing the option
     */
    public function testSubmitValidDataWithGrandChildren()
    {
        $formData = [
            'categoryAssignments' => ['registry_1' => '4'],
        ];

        $form = $this->factory->create(CategorizableType::class, new CategorizableEntity(), [
            'em' => $this->em,
            'includeGrandChildren' => true
        ]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference(CategoryEntity::class, 4);
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

    public function testSubmitValidDataWithAllChildren()
    {
        $formData = [
            'categoryAssignments' => ['registry_1' => '4'],
        ];

        $form = $this->factory->create(CategorizableType::class, new CategorizableEntity(), [
            'em' => $this->em,
            'direct' => false
        ]);

        $expectedObject = new CategorizableEntity();
        $categoryAssignments = new ArrayCollection();
        $assignedCategory = $this->em->getReference(CategoryEntity::class, 4);
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

    /**
     * submitting invalid selection results in NO assignments
     * The child of child selection should not be available with direct = true (default)
     */
    public function testSubmitInvalidDataWithNoChildren()
    {
        $formData = [
            'categoryAssignments' => ['registry_1' => '4'], // child of child
        ];

        $form = $this->factory->create(CategorizableType::class, new CategorizableEntity(), [
            'em' => $this->em,
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
        $registry = $this->getMockBuilder(ManagerRegistry::class)->getMock();
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($name))
            ->will($this->returnValue($em));

        return $registry;
    }

    protected function generateCategoryRegistry($now)
    {
        $registry = new CategoryRegistryEntity();
        $registry->setId(1);
        $registry->setModname('AcmeFooModule');
        $registry->setEntityname('CategorizableEntity');
        $registry->setProperty('Main');
        $registry->setCr_date($now);
        $registry->setLu_date($now);
        $rootCategory = $this->em->getRepository(CategoryEntity::class)->find(1);
        $registry->setCategory($rootCategory);
        $this->em->persist($registry);
        $this->em->flush();
    }

    protected function generateCategories($now)
    {
        // root
        $root = new CategoryEntity();
        $root->setId(1);
        $root->setName('root');
        $root->setDisplay_name(['en' => 'root']);
        $root->setCr_date($now);
        $root->setLu_date($now);
        $this->em->getRepository(CategoryEntity::class)->persistAsFirstChild($root);

        // first child
        $a = new CategoryEntity();
        $a->setId(2);
        $a->setParent($root);
        $a->setName('a');
        $a->setDisplay_name(['en' => 'a']);
        $a->setCr_date($now);
        $a->setLu_date($now);
        $this->em->getRepository(CategoryEntity::class)->persistAsFirstChildOf($a, $root);

        // second child
        $b = new CategoryEntity();
        $b->setId(3);
        $b->setParent($root);
        $b->setName('b');
        $b->setDisplay_name(['en' => 'b']);
        $b->setCr_date($now);
        $b->setLu_date($now);
        $this->em->getRepository(CategoryEntity::class)->persistAsLastChildOf($b, $root);

        // child of first child (grand child)
        $aa = new CategoryEntity();
        $aa->setId(4);
        $aa->setParent($a);
        $aa->setName('aa');
        $aa->setDisplay_name(['en' => 'aa']);
        $aa->setCr_date($now);
        $aa->setLu_date($now);
        $this->em->getRepository(CategoryEntity::class)->persistAsFirstChildOf($aa, $a);

        $this->em->flush();
    }
}
