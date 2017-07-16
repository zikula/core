<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Helper\TreeMapHelper;
use Zikula\Core\AbstractExtensionInstaller;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Installation and upgrade routines for the categories module.
 */
class CategoriesModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Initialise the categories module.
     *
     * @return bool true if successful, false otherwise
     */
    public function install()
    {
        $entities = [
            'Zikula\CategoriesModule\Entity\CategoryEntity',
            'Zikula\CategoriesModule\Entity\CategoryAttributeEntity',
            'Zikula\CategoriesModule\Entity\CategoryRegistryEntity'
        ];

        try {
            $this->schemaTool->create($entities);
        } catch (\Exception $e) {
            return false;
        }

        /**
         * explicitly set admin as user to be set as `lu_uid` and `cr_uid` fields. Normally this would be taken care of
         * by the BlameListener but during installation from the CLI this listener is not available
         */
        $adminUserEntity = $this->entityManager->getReference('ZikulaUsersModule:UserEntity', 2);

        // insert default data
        $this->insertDefaultData($adminUserEntity);

        // Set autonumber to 10000 (for DB's that support autonumber fields)
        $cat = new CategoryEntity();
        $cat->setId(9999);
        $cat->setLu_uid($adminUserEntity);
        $cat->setCr_uid($adminUserEntity);
        $this->entityManager->persist($cat);
        $this->entityManager->flush();
        $this->entityManager->remove($cat);
        $this->entityManager->flush();

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param string $oldversion version number string to upgrade from
     *
     * @return bool|int true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        $connection = $this->entityManager->getConnection();
        $this->schemaTool->update([
            CategoryEntity::class
        ]);

        switch ($oldversion) {
            case '1.1':
            case '1.2':
            case '1.2.1':
                try {
                    $this->schemaTool->create(['Zikula\CategoriesModule\Entity\CategoryAttributeEntity']);
                } catch (\Exception $e) {
                }
                // rename old tablename column for Core 1.4.0
                $sql = 'ALTER TABLE categories_registry CHANGE `tablename` `entityname` varchar (60) NOT NULL DEFAULT \'\'';
                $connection->executeQuery($sql);

                $this->migrateAttributesFromObjectData();
            case '1.2.2':
            case '1.2.3':
            case '1.3.0':
                $this->delVars();
                $helper = new TreeMapHelper($this->container->get('doctrine'));
                $helper->map(); // updates NestedTree values in entities
                $connection->executeQuery('UPDATE categories_category SET `tree_root` = 1 WHERE 1');

            case '1.3.1':
                // future
        }

        return true;
    }

    /**
     * delete module
     *
     * @return bool false as this module cannot be deleted
     */
    public function uninstall()
    {
        // Not allowed to delete
        return false;
    }

    /**
     * insert default data
     *
     * @param UserEntity $adminUserEntity
     *
     * @return void
     */
    private function insertDefaultData(UserEntity $adminUserEntity)
    {
        $categoryData = $this->getDefaultCategoryData();
        $categoryObjectMap = [];
        /**
         * @var ClassMetadata $categoryMetaData
         */
        $categoryMetaData = $this->entityManager->getClassMetaData(CategoryEntity::class);
        // disable auto-generation of keys to allow manual setting from this data set.
        $categoryMetaData->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);

        foreach ($categoryData as $data) {
            $data['parent'] = $data['parent_id'] > 0 && isset($categoryObjectMap[$data['parent_id']]) ? $categoryObjectMap[$data['parent_id']] : null;
            unset($data['parent_id']);
            $attributes = isset($data['attributes']) ? $data['attributes'] : [];
            unset($data['attributes']);

            $category = new CategoryEntity();
            $category->merge($data);
            // see note above about setting these fields during installation
            $category->setCr_uid($adminUserEntity);
            $category->setLu_uid($adminUserEntity);
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

    /**
     * @return array list of data arrays for default categories
     */
    private function getDefaultCategoryData()
    {
        $categoryData = [];
        $categoryData[] = [
            'id' => 1,
            'parent_id' => 0,
            'is_locked' => true,
            'is_leaf' => false,
            'value' => '',
            'name' => '__SYSTEM__',
            'display_name' => $this->localize($this->__('Category root')),
            'display_desc' => '',
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 2,
            'parent_id' => 1,
            'is_locked' => false,
            'is_leaf' => false,
            'value' => '',
            'name' => 'Modules',
            'display_name' => $this->localize($this->__('Modules')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 3,
            'parent_id' => 1,
            'is_locked' => false,
            'is_leaf' => false,
            'value' => '',
            'name' => 'General',
            'display_name' => $this->localize($this->__('General')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 10,
            'parent_id' => 3,
            'is_locked' => false,
            'is_leaf' => false,
            'value' => '',
            'name' => 'Publication Status (extended)',
            'display_name' => $this->localize($this->__('Publication status (extended)')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 11,
            'parent_id' => 10,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => 'P',
            'name' => 'Pending',
            'display_name' => $this->localize($this->__('Pending')),
            'display_desc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'P']
        ];
        $categoryData[] = [
            'id' => 12,
            'parent_id' => 10,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => 'C',
            'name' => 'Checked',
            'display_name' => $this->localize($this->__('Checked')),
            'display_desc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'C']
        ];
        $categoryData[] = [
            'id' => 13,
            'parent_id' => 10,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => 'A',
            'name' => 'Approved',
            'display_name' => $this->localize($this->__('Approved')),
            'display_desc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'A']
        ];
        $categoryData[] = [
            'id' => 14,
            'parent_id' => 10,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => 'O',
            'name' => 'On-line',
            'display_name' => $this->localize($this->__('On-line')),
            'display_desc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'O']
        ];
        $categoryData[] = [
            'id' => 15,
            'parent_id' => 10,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => 'R',
            'name' => 'Rejected',
            'display_name' => $this->localize($this->__('Rejected')),
            'display_desc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'R']
        ];
        $categoryData[] = [
            'id' => 25,
            'parent_id' => 3,
            'is_locked' => false,
            'is_leaf' => false,
            'value' => '',
            'name' => 'ActiveStatus',
            'display_name' => $this->localize($this->__('Activity status')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 26,
            'parent_id' => 25,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => 'A',
            'name' => 'Active',
            'display_name' => $this->localize($this->__('Active')),
            'display_desc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'A']
        ];
        $categoryData[] = [
            'id' => 27,
            'parent_id' => 25,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => 'I',
            'name' => 'Inactive',
            'display_name' => $this->localize($this->__('Inactive')),
            'display_desc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'I']
        ];
        $categoryData[] = [
            'id' => 28,
            'parent_id' => 3,
            'is_locked' => false,
            'is_leaf' => false,
            'value' => '',
            'name' => 'Publication status (basic)',
            'display_name' => $this->localize($this->__('Publication status (basic)')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 29,
            'parent_id' => 28,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => 'P',
            'name' => 'Pending',
            'display_name' => $this->localize($this->__('Pending')),
            'display_desc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'P']
        ];
        $categoryData[] = [
            'id' => 30,
            'parent_id' => 28,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => 'A',
            'name' => 'Approved',
            'display_name' => $this->localize($this->__('Approved')),
            'display_desc' => $this->localize(),
            'status' => 'A',
            'attributes' => ['code' => 'A']
        ];
        $categoryData[] = [
            'id' => 32,
            'parent_id' => 2,
            'is_locked' => false,
            'is_leaf' => false,
            'value' => '',
            'name' => 'Global',
            'display_name' => $this->localize($this->__('Global')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 33,
            'parent_id' => 32,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => '',
            'name' => 'Blogging',
            'display_name' => $this->localize($this->__('Blogging')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 34,
            'parent_id' => 32,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => '',
            'name' => 'Music and audio',
            'display_name' => $this->localize($this->__('Music and audio')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 35,
            'parent_id' => 32,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => '',
            'name' => 'Art and photography',
            'display_name' => $this->localize($this->__('Art and photography')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 36,
            'parent_id' => 32,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => '',
            'name' => 'Writing and thinking',
            'display_name' => $this->localize($this->__('Writing and thinking')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 37,
            'parent_id' => 32,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => '',
            'name' => 'Communications and media',
            'display_name' => $this->localize($this->__('Communications and media')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 38,
            'parent_id' => 32,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => '',
            'name' => 'Travel and culture',
            'display_name' => $this->localize($this->__('Travel and culture')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 39,
            'parent_id' => 32,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => '',
            'name' => 'Science and technology',
            'display_name' => $this->localize($this->__('Science and technology')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 40,
            'parent_id' => 32,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => '',
            'name' => 'Sport and activities',
            'display_name' => $this->localize($this->__('Sport and activities')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $categoryData[] = [
            'id' => 41,
            'parent_id' => 32,
            'is_locked' => false,
            'is_leaf' => true,
            'value' => '',
            'name' => 'Business and work',
            'display_name' => $this->localize($this->__('Business and work')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];

        return $categoryData;
    }

    /**
     * @param string $value
     * @return array the localised array
     */
    public function localize($value = '')
    {
        $values = [];
        foreach ($this->container->get('zikula_settings_module.locale_api')->getSupportedLocales() as $code) {
            $values[$code] = $this->__(/** @Ignore */$value, 'zikula', $code);
        }

        return $values;
    }

    /**
     * migrates all attributes belonging to categories to the new `categories_attributes` table
     * regardless of the module they are attached to.
     *
     * It does _not_ remove the data from the `objectdata_attributes` table.
     *
     * @return void
     */
    private function migrateAttributesFromObjectData()
    {
        $attributes = $this->entityManager->getConnection()->fetchAll("SELECT * FROM objectdata_attributes WHERE object_type = 'categories_category'");
        foreach ($attributes as $attribute) {
            $category = $this->entityManager->getRepository('Zikula\CategoriesModule\Entity\CategoryEntity')
                ->findOneBy(['id' => $attribute['object_id']]);
            if (isset($category) && is_object($category)) {
                $category->setAttribute($attribute['attribute_name'], $attribute['value']);
            }
        }
        $this->entityManager->flush();
    }
}
