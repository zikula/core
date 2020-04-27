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

namespace Zikula\CategoriesModule;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\CategoriesModule\Entity\CategoryAttributeEntity;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;
use Zikula\CategoriesModule\Helper\TreeMapHelper;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Installation and upgrade routines for the categories module.
 */
class CategoriesModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var array
     */
    private $entities = [
        CategoryEntity::class,
        CategoryAttributeEntity::class,
        CategoryRegistryEntity::class
    ];

    public function __construct(
        LocaleApiInterface $localeApi,
        AbstractExtension $extension,
        ManagerRegistry $managerRegistry,
        SchemaHelper $schemaTool,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->localeApi = $localeApi;
        parent::__construct($extension, $managerRegistry, $schemaTool, $requestStack, $translator, $variableApi);
    }

    public function install(): bool
    {
        $this->schemaTool->create($this->entities);

        /**
         * explicitly set admin as user to be set as `updatedBy` and `createdBy` fields. Normally this would be taken care of
         * by the BlameListener but during installation from the CLI this listener is not available
         */
        /** @var UserEntity $adminUserEntity */
        $adminUserEntity = $this->entityManager->getReference('ZikulaUsersModule:UserEntity', 2);

        // insert default data
        $this->insertDefaultData($adminUserEntity);

        // Set autonumber to 10000 (for DB's that support autonumber fields)
        $cat = new CategoryEntity();
        $cat->setId(9999);
        $cat->setUpdatedBy($adminUserEntity);
        $cat->setCreatedBy($adminUserEntity);
        $this->entityManager->persist($cat);
        $this->entityManager->flush();
        $this->entityManager->remove($cat);
        $this->entityManager->flush();

        // Initialisation successful
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        $connection = $this->entityManager->getConnection();
        $this->schemaTool->update([
            CategoryEntity::class
        ]);

        switch ($oldVersion) {
            case '1.1':
            case '1.2':
            case '1.2.1':
                $this->schemaTool->create([
                    CategoryAttributeEntity::class
                ]);

                // rename old tablename column for Core 1.4.0
                $sql = 'ALTER TABLE categories_registry CHANGE `tablename` `entityname` varchar (60) NOT NULL DEFAULT \'\'';
                $connection->executeQuery($sql);

                $this->migrateAttributesFromObjectData();
            case '1.2.2':
            case '1.2.3':
            case '1.3.0':
                $this->delVars();
                /** @var CategoryRepositoryInterface $categoryRepository */
                $categoryRepository = $this->entityManager->getRepository(CategoryEntity::class);
                $helper = new TreeMapHelper($this->managerRegistry, $categoryRepository);
                $helper->map(); // updates NestedTree values in entities
                $connection->executeQuery('UPDATE categories_category SET `tree_root` = 1 WHERE 1');

            case '1.3.1':// shipped with Core-2.0.15
                // future
        }

        return true;
    }

    public function uninstall(): bool
    {
        // Not allowed to delete
        return false;
    }

    private function insertDefaultData(UserEntity $adminUserEntity): void
    {
        $categoryData = $this->getDefaultCategoryData();
        $categoryObjectMap = [];
        /**
         * @var ClassMetadata
         */
        $categoryMetaData = $this->entityManager->getClassMetaData(CategoryEntity::class);
        // disable auto-generation of keys to allow manual setting from this data set.
        $categoryMetaData->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);

        foreach ($categoryData as $data) {
            $data['parent'] = $data['parent_id'] > 0 && isset($categoryObjectMap[$data['parent_id']]) ? $categoryObjectMap[$data['parent_id']] : null;
            unset($data['parent_id']);
            $attributes = $data['attributes'] ?? [];
            unset($data['attributes']);

            $category = new CategoryEntity();
            $category->merge($data);
            // see note above about setting these fields during installation
            $category->setCreatedBy($adminUserEntity);
            $category->setUpdatedBy($adminUserEntity);
            $this->entityManager->persist($category);

            $categoryObjectMap[$data['id']] = $category;

            if (isset($attributes)) {
                foreach ($attributes as $key => $value) {
                    $category->setAttribute($key, $value);
                }
            }
            // unset this so it doesn't persist in the next foreach
            unset($attributes);
        }

        $this->entityManager->flush();
        $categoryMetaData->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
    }

    private function getDefaultCategoryData(): array
    {
        $categoryData = [];
        $categoryData[] = [
            'id' => 1,
            'parent_id' => 0,
            'locked' => true,
            'leaf' => false,
            'value' => '',
            'name' => '__SYSTEM__',
            'displayName' => $this->localize($this->trans('Category root')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 2,
            'parent_id' => 1,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'Modules',
            'displayName' => $this->localize($this->trans('Modules')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 3,
            'parent_id' => 1,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'General',
            'displayName' => $this->localize($this->trans('General')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 10,
            'parent_id' => 3,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'Publication Status (extended)',
            'displayName' => $this->localize($this->trans('Publication status (extended)')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 11,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'P',
            'name' => 'Pending',
            'displayName' => $this->localize($this->trans('Pending')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'P']
        ];
        $categoryData[] = [
            'id' => 12,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'C',
            'name' => 'Checked',
            'displayName' => $this->localize($this->trans('Checked')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'C']
        ];
        $categoryData[] = [
            'id' => 13,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'A',
            'name' => 'Approved',
            'displayName' => $this->localize($this->trans('Approved')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'A']
        ];
        $categoryData[] = [
            'id' => 14,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'O',
            'name' => 'On-line',
            'displayName' => $this->localize($this->trans('On-line')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'O']
        ];
        $categoryData[] = [
            'id' => 15,
            'parent_id' => 10,
            'locked' => false,
            'leaf' => true,
            'value' => 'R',
            'name' => 'Rejected',
            'displayName' => $this->localize($this->trans('Rejected')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'R']
        ];
        $categoryData[] = [
            'id' => 25,
            'parent_id' => 3,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'ActiveStatus',
            'displayName' => $this->localize($this->trans('Activity status')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 26,
            'parent_id' => 25,
            'locked' => false,
            'leaf' => true,
            'value' => 'A',
            'name' => 'Active',
            'displayName' => $this->localize($this->trans('Active')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'A']
        ];
        $categoryData[] = [
            'id' => 27,
            'parent_id' => 25,
            'locked' => false,
            'leaf' => true,
            'value' => 'I',
            'name' => 'Inactive',
            'displayName' => $this->localize($this->trans('Inactive')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'I']
        ];
        $categoryData[] = [
            'id' => 28,
            'parent_id' => 3,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'Publication status (basic)',
            'displayName' => $this->localize($this->trans('Publication status (basic)')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 29,
            'parent_id' => 28,
            'locked' => false,
            'leaf' => true,
            'value' => 'P',
            'name' => 'Pending',
            'displayName' => $this->localize($this->trans('Pending')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'P']
        ];
        $categoryData[] = [
            'id' => 30,
            'parent_id' => 28,
            'locked' => false,
            'leaf' => true,
            'value' => 'A',
            'name' => 'Approved',
            'displayName' => $this->localize($this->trans('Approved')),
            'displayDesc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'A']
        ];
        $categoryData[] = [
            'id' => 32,
            'parent_id' => 2,
            'locked' => false,
            'leaf' => false,
            'value' => '',
            'name' => 'Global',
            'displayName' => $this->localize($this->trans('Global')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 33,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Blogging',
            'displayName' => $this->localize($this->trans('Blogging')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 34,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Music and audio',
            'displayName' => $this->localize($this->trans('Music and audio')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 35,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Art and photography',
            'displayName' => $this->localize($this->trans('Art and photography')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 36,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Writing and thinking',
            'displayName' => $this->localize($this->trans('Writing and thinking')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 37,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Communications and media',
            'displayName' => $this->localize($this->trans('Communications and media')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 38,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Travel and culture',
            'displayName' => $this->localize($this->trans('Travel and culture')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 39,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Science and technology',
            'displayName' => $this->localize($this->trans('Science and technology')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 40,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Sport and activities',
            'displayName' => $this->localize($this->trans('Sport and activities')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 41,
            'parent_id' => 32,
            'locked' => false,
            'leaf' => true,
            'value' => '',
            'name' => 'Business and work',
            'displayName' => $this->localize($this->trans('Business and work')),
            'displayDesc' => $this->localize(),
            'status' => 'A'
        ];

        return $categoryData;
    }

    public function localize(string $value = ''): array
    {
        $values = [];
        foreach ($this->localeApi->getSupportedLocales() as $code) {
            $values[$code] = $this->trans(/** @Ignore */$value, [], 'zikula', $code);
        }

        return $values;
    }

    /**
     * Migrates all attributes belonging to categories to the new `categories_attributes` table
     * regardless of the module they are attached to.
     *
     * It does _not_ remove the data from the `objectdata_attributes` table.
     */
    private function migrateAttributesFromObjectData(): void
    {
        $attributes = $this->entityManager->getConnection()->fetchAll("SELECT * FROM objectdata_attributes WHERE object_type = 'categories_category'");
        foreach ($attributes as $attribute) {
            $category = $this->entityManager->getRepository(CategoryEntity::class)
                ->findOneBy(['id' => $attribute['object_id']]);
            if (isset($category) && is_object($category)) {
                $category->setAttribute($attribute['attribute_name'], $attribute['value']);
            }
        }
        $this->entityManager->flush();
    }
}
