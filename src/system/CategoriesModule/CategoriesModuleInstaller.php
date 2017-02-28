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

use DoctrineUtil;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Helper\TreeMapHelper;
use Zikula\Core\AbstractExtensionInstaller;

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
         * This entity is only used to install the table and it
         * is @deprecated as of 1.4.0 because the Objectdata paradigm
         * is being removed at 2.0.0
         */
        try {
            $this->schemaTool->create(['Zikula\CategoriesModule\Entity\CategoriesMapobj']);
        } catch (\Exception $e) {
            return false;
        }

        /**
         * explicitly set admin as user to be set as `lu_uid` and `cr_uid` fields. Normally this would be taken care of
         * by the BlameListener but during installation from the CLI this listener is not available
         */
        $adminUserObj = $this->entityManager->getReference('ZikulaUsersModule:UserEntity', 2);

        // insert default data
        $this->insertData_10($adminUserObj);

        // Set autonumber to 10000 (for DB's that support autonumber fields)
        $cat = new CategoryEntity();
        $cat->setId(9999);
        $cat->setLu_uid($adminUserObj);
        $cat->setCr_uid($adminUserObj);
        $this->entityManager->persist($cat);
        $this->entityManager->flush();
        $this->entityManager->remove($cat);
        $this->entityManager->flush();

        // set module vars
        $this->setVar('userrootcat', 31);
        $this->setVar('allowusercatedit', 0);
        $this->setVar('autocreateusercat', 0);
        $this->setVar('autocreateuserdefaultcat', 0);
        $this->setVar('permissionsall', 0);
        $this->setVar('userdefaultcatname', $this->__('Default'));

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
        switch ($oldversion) {
            case '1.1':
            case '1.2':
                // new column used in doctrine categorisable template
                DoctrineUtil::createColumn('categories_mapobj', 'reg_property', ['type' => 'string', 'length' => 60], false);
            case '1.2.1':
                try {
                    $this->schemaTool->create(['Zikula\CategoriesModule\Entity\CategoryAttributeEntity']);
                    $this->schemaTool->update(['Zikula\CategoriesModule\Entity\CategoryEntity']);
                } catch (\Exception $e) {
                }
                // rename old tablename column for Core 1.4.0
                $sql = 'ALTER TABLE categories_registry CHANGE `tablename` `entityname` varchar (60) NOT NULL DEFAULT \'\'';
                $connection->executeQuery($sql);

                $this->migrateAttributesFromObjectData();
            case '1.2.2':
            case '1.2.3':
            case '1.3.0':
                $modVars = $this->getVars();
                $usersModuleRootCategory = $this->container->get('zikula_categories_module.category_repository')->find(31);
                $modVars['userrootcat'] = $usersModuleRootCategory->getId();
                foreach (['allowusercatedit', 'autocreateusercat', 'autocreateuserdefaultcat', 'permissionsall'] as $boolVar) {
                    $modVars[$boolVar] = isset($modVars[$boolVar]) ? (bool)$modVars[$boolVar] : false;
                }
                $this->setVars($modVars);
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
     * @param $adminUserObj
     *
     * @return void
     */
    public function insertData_10($adminUserObj)
    {
        $objArray = [];
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
            'id' => 31,
            'parent_id' => 1,
            'is_locked' => false,
            'is_leaf' => false,
            'value' => '',
            'name' => 'ZikulaUsersModule',
            'display_name' => $this->localize($this->__('Users')),
            'display_desc' => $this->localize(),
            'status' => 'A'
        ];
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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
        $objArray[] = [
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

        foreach ($objArray as $obj) {
            $category = new CategoryEntity();

            // disable auto-generation of keys to allow manual setting from this data set.
            $metadata = $this->entityManager->getClassMetaData(get_class($category));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_NONE);

            if ($obj['parent_id'] == 0) {
                $obj['parent'] = null;
            } else {
                $obj['parent'] = $this->entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $obj['parent_id']);
            }
            unset($obj['parent_id']);
            $attributes = isset($obj['attributes']) ? $obj['attributes'] : [];
            unset($obj['attributes']);

            $category->merge($obj);
            // see note above about setting these fields during installation
            $category->setCr_uid($adminUserObj);
            $category->setLu_uid($adminUserObj);
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_AUTO);

            if (isset($attributes)) {
                foreach ($attributes as $key => $value) {
                    $category->setAttribute($key, $value);
                }
            }
            // unset this so it doesn't persist in the next foreach
            unset($attributes);
        }

        $this->entityManager->flush();
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
