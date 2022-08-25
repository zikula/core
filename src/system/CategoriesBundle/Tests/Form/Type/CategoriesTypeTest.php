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

namespace Zikula\CategoriesBundle\Tests\Form\Type;

use DateTime;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Gedmo\Tree\TreeListener;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Zikula\CategoriesBundle\Entity\CategoryEntity;
use Zikula\CategoriesBundle\Entity\CategoryRegistryEntity;
use Zikula\CategoriesBundle\Form\Type\CategoriesType;
use Zikula\CategoriesBundle\Repository\CategoryRepositoryInterface;
use Zikula\CategoriesBundle\RepositoryInterface\CategoryRegistryRepositoryInterface;
use Zikula\CategoriesBundle\Tests\Fixtures\CategorizableEntity;
use Zikula\CategoriesBundle\Tests\Fixtures\CategorizableType;
use Zikula\CategoriesBundle\Tests\Fixtures\CategoryAssignmentEntity;

/**
 * @see https://symfony.com/doc/current/form/unit_testing.html
 */
class CategoriesTypeTest extends TypeTestCase
{
    private EntityManager $em;

    private ManagerRegistry $emRegistry;

    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');

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
        } catch (Exception $exception) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (Exception $exception) {
        }
        $now = new DateTime();
        $this->generateCategories($now);
        $this->generateCategoryRegistry($now);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em = null;
        $this->emRegistry = null;
    }

    protected function getExtensions(): array
    {
        /** @var CategoryRegistryRepositoryInterface $repository */
        $repository = $this->emRegistry->getRepository(CategoryRegistryEntity::class);

        $request = new Request([], [], [], [], [], [], json_encode([
            'foo' => 'bar'
        ]));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $type = new CategoriesType($repository, $requestStack, $this->em);

        return array_merge(parent::getExtensions(), [
            new PreloadedExtension([$type], []),
            new DoctrineOrmExtension($this->emRegistry),
        ]);
    }

    public function testClassOptionIsRequired(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->factory->createNamed('name', CategoriesType::class);
    }

    public function testSubmitValidData(): void
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

    public function testSubmitMultipleValidData(): void
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

    public function testSubmitWithExistingData(): void
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

    public function testSubmitMultipleWithExistingData(): void
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

    public function testSubmitSingleWithMultipleExistingData(): void
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

    public function testSubmitEmptyWithExistingData(): void
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

    public function testSubmitEmptyMultipleWithExistingData(): void
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

    public function testSubmitValidDataWithAllChildren(): void
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
     * Submitting invalid selection results in NO assignments
     * The child of child selection should not be available with direct = true (default)
     */
    public function testSubmitInvalidDataWithNoChildren(): void
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

    protected function createRegistryMock(string $name, EntityManagerInterface $em): ManagerRegistry
    {
        $registry = $this->getMockBuilder(ManagerRegistry::class)->getMock();
        $registry
            ->method('getManager')
            ->with($this->equalTo($name))
            ->willReturn($em);

        return $registry;
    }

    protected function generateCategoryRegistry(DateTime $now): void
    {
        $registry = (new CategoryRegistryEntity())
            ->setId(1)
            ->setBundleName('AcmeFooBundle')
            ->setEntityName('CategorizableEntity')
            ->setProperty('Main')
            ->setCreatedDate($now)
            ->setUpdatedDate($now);
        /** @var CategoryEntity $rootCategory */
        $rootCategory = $this->emRegistry->getRepository(CategoryEntity::class)->find(1);
        $registry->setCategory($rootCategory);
        $this->em->persist($registry);
        $this->em->flush();
    }

    protected function generateCategories(DateTime $now): void
    {
        /** @var CategoryRepositoryInterface $repository */
        $repository = $this->emRegistry->getRepository(CategoryEntity::class);

        // root
        $root = (new CategoryEntity())
            ->setId(1)
            ->setName('root')
            ->setDisplayName(['en' => 'root'])
            ->setCreatedDate($now)
            ->setUpdatedDate($now);
        $repository->persistAsFirstChild($root);

        // first child
        $a = (new CategoryEntity())
            ->setId(2)
            ->setParent($root)
            ->setName('a')
            ->setDisplayName(['en' => 'a'])
            ->setCreatedDate($now)
            ->setUpdatedDate($now);
        $repository->persistAsFirstChildOf($a, $root);

        // second child
        $b = (new CategoryEntity())
            ->setId(3)
            ->setParent($root)
            ->setName('b')
            ->setDisplayName(['en' => 'b'])
            ->setCreatedDate($now)
            ->setUpdatedDate($now);
        $repository->persistAsLastChildOf($b, $root);

        // child of first child (grand child)
        $aa = (new CategoryEntity())
            ->setId(4)
            ->setParent($a)
            ->setName('aa')
            ->setDisplayName(['en' => 'aa'])
            ->setCreatedDate($now)
            ->setUpdatedDate($now);
        $repository->persistAsFirstChildOf($aa, $a);

        $this->em->flush();
    }
}
