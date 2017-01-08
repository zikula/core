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
        $this->setVar('userrootcat', '/__SYSTEM__/Users');
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
        switch ($oldversion) {
            case '1.1':
            case '1.2':
                // new column used in doctrine categorisable template
                DoctrineUtil::createColumn('categories_mapobj', 'reg_property', ['type' => 'string', 'length' => 60], false);
            case '1.2.1':
                try {
                    $this->schemaTool->create(['Zikula\CategoriesModule\Entity\CategoryAttributeEntity']);
                } catch (\Exception $e) {
                }
                // rename old tablename column for Core 1.4.0
                $connection = $this->entityManager->getConnection();
                $sql = 'ALTER TABLE categories_registry CHANGE `tablename` `entityname` varchar (60) NOT NULL DEFAULT \'\'';
                $connection->executeQuery($sql);

                $this->migrateAttributesFromObjectData();
            case '1.2.2':
            case '1.2.3':
            case '1.3.0':
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
            'is_locked' => 1,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 1,
            'name' => '__SYSTEM__',
            'display_name' => '',
            'display_desc' => '',
            'path' => '/__SYSTEM__',
            'ipath' => '/1',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 2,
            'parent_id' => 1,
            'is_locked' => 0,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 2,
            'name' => 'Modules',
            'display_name' => $this->makeDisplayName($this->__('Modules')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules',
            'ipath' => '/1/2',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 3,
            'parent_id' => 1,
            'is_locked' => 0,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 3,
            'name' => 'General',
            'display_name' => $this->makeDisplayName($this->__('General')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General',
            'ipath' => '/1/3',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 4,
            'parent_id' => 3,
            'is_locked' => 0,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 4,
            'name' => 'YesNo',
            'display_name' => $this->makeDisplayName($this->__('Yes/No')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/YesNo',
            'ipath' => '/1/3/4',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 5,
            'parent_id' => 4,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'Y',
            'sort_value' => 5,
            'name' => '1 - Yes',
            'display_name' => '',
            'display_desc' => '',
            'path' => '/__SYSTEM__/General/YesNo/1 - Yes',
            'ipath' => '/1/3/4/5',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'Y']
        ];
        $objArray[] = [
            'id' => 6,
            'parent_id' => 4,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'N',
            'sort_value' => 6,
            'name' => '2 - No',
            'display_name' => '',
            'display_desc' => '',
            'path' => '/__SYSTEM__/General/YesNo/2 - No',
            'ipath' => '/1/3/4/6',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'N']
        ];
        $objArray[] = [
            'id' => 10,
            'parent_id' => 3,
            'is_locked' => 0,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 10,
            'name' => 'Publication Status (extended)',
            'display_name' => $this->makeDisplayName($this->__('Publication status (extended)')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Publication Status Extended',
            'ipath' => '/1/3/10',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 11,
            'parent_id' => 10,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'P',
            'sort_value' => 11,
            'name' => 'Pending',
            'display_name' => $this->makeDisplayName($this->__('Pending')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Publication Status Extended/Pending',
            'ipath' => '/1/3/10/11',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'P']
        ];
        $objArray[] = [
            'id' => 12,
            'parent_id' => 10,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'C',
            'sort_value' => 12,
            'name' => 'Checked',
            'display_name' => $this->makeDisplayName($this->__('Checked')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Publication Status Extended/Checked',
            'ipath' => '/1/3/10/12',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'C']
        ];
        $objArray[] = [
            'id' => 13,
            'parent_id' => 10,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'A',
            'sort_value' => 13,
            'name' => 'Approved',
            'display_name' => $this->makeDisplayName($this->__('Approved')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Publication Status Extended/Approved',
            'ipath' => '/1/3/10/13',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'A']
        ];
        $objArray[] = [
            'id' => 14,
            'parent_id' => 10,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'O',
            'sort_value' => 14,
            'name' => 'On-line',
            'display_name' => $this->makeDisplayName($this->__('On-line')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Publication Status Extended/Online',
            'ipath' => '/1/3/10/14',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'O']
        ];
        $objArray[] = [
            'id' => 15,
            'parent_id' => 10,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'R',
            'sort_value' => 15,
            'name' => 'Rejected',
            'display_name' => $this->makeDisplayName($this->__('Rejected')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Publication Status Extended/Rejected',
            'ipath' => '/1/3/10/15',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'R']
        ];
        $objArray[] = [
            'id' => 16,
            'parent_id' => 3,
            'is_locked' => 0,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 16,
            'name' => 'Gender',
            'display_name' => $this->makeDisplayName($this->__('Gender')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Gender',
            'ipath' => '/1/3/16',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 17,
            'parent_id' => 16,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'M',
            'sort_value' => 17,
            'name' => 'Male',
            'display_name' => $this->makeDisplayName($this->__('Male')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Gender/Male',
            'ipath' => '/1/3/16/17',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'M']
        ];
        $objArray[] = [
            'id' => 18,
            'parent_id' => 16,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'F',
            'sort_value' => 18,
            'name' => 'Female',
            'display_name' => $this->makeDisplayName($this->__('Female')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Gender/Female',
            'ipath' => '/1/3/16/18',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'F']
        ];
        $objArray[] = [
            'id' => 19,
            'parent_id' => 3,
            'is_locked' => 0,
            'is_leaf' => 0,
            'sort_value' => 19,
            'value' => '',
            'name' => 'Title',
            'display_name' => $this->makeDisplayName($this->__('Title')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Title',
            'ipath' => '/1/3/19',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 20,
            'parent_id' => 19,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'Mr',
            'sort_value' => 20,
            'name' => 'Mr',
            'display_name' => $this->makeDisplayName($this->__('Mr.')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Title/Mr',
            'ipath' => '/1/3/19/20',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 21,
            'parent_id' => 19,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'Mrs',
            'sort_value' => 21,
            'name' => 'Mrs',
            'display_name' => $this->makeDisplayName($this->__('Mrs.')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Title/Mrs',
            'ipath' => '/1/3/19/21',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 22,
            'parent_id' => 19,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'Ms',
            'sort_value' => 22,
            'name' => 'Ms',
            'display_name' => $this->makeDisplayName($this->__('Ms.')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Title/Ms',
            'ipath' => '/1/3/19/22',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 23,
            'parent_id' => 19,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'Miss',
            'sort_value' => 23,
            'name' => 'Miss',
            'display_name' => $this->makeDisplayName($this->__('Miss')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Title/Miss',
            'ipath' => '/1/3/19/23',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 24,
            'parent_id' => 19,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'Dr',
            'sort_value' => 24,
            'name' => 'Dr',
            'display_name' => $this->makeDisplayName($this->__('Dr.')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Title/Dr',
            'ipath' => '/1/3/19/24',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 25,
            'parent_id' => 3,
            'is_locked' => 0,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 25,
            'name' => 'ActiveStatus',
            'display_name' => $this->makeDisplayName($this->__('Activity status')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/ActiveStatus',
            'ipath' => '/1/3/25',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 26,
            'parent_id' => 25,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'A',
            'sort_value' => 26,
            'name' => 'Active',
            'display_name' => $this->makeDisplayName($this->__('Active')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/ActiveStatus/Active',
            'ipath' => '/1/3/25/26',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'A']
        ];
        $objArray[] = [
            'id' => 27,
            'parent_id' => 25,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'I',
            'sort_value' => 27,
            'name' => 'Inactive',
            'display_name' => $this->makeDisplayName($this->__('Inactive')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/ActiveStatus/Inactive',
            'ipath' => '/1/3/25/27',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'I']
        ];
        $objArray[] = [
            'id' => 28,
            'parent_id' => 3,
            'is_locked' => 0,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 28,
            'name' => 'Publication status (basic)',
            'display_name' => $this->makeDisplayName($this->__('Publication status (basic)')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Publication Status Basic',
            'ipath' => '/1/3/28',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 29,
            'parent_id' => 28,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'P',
            'sort_value' => 29,
            'name' => 'Pending',
            'display_name' => $this->makeDisplayName($this->__('Pending')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Publication Status Basic/Pending',
            'ipath' => '/1/3/28/29',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'P']
        ];
        $objArray[] = [
            'id' => 30,
            'parent_id' => 28,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => 'A',
            'sort_value' => 30,
            'name' => 'Approved',
            'display_name' => $this->makeDisplayName($this->__('Approved')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/General/Publication Status Basic/Approved',
            'ipath' => '/1/3/28/30',
            'status' => 'A',
            '__ATTRIBUTES__' => ['code' => 'A']
        ];
        $objArray[] = [
            'id' => 31,
            'parent_id' => 1,
            'is_locked' => 0,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 31,
            'name' => 'ZikulaUsersModule',
            'display_name' => $this->makeDisplayName($this->__('Users')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Users',
            'ipath' => '/1/31',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 32,
            'parent_id' => 2,
            'is_locked' => 0,
            'is_leaf' => 0,
            'value' => '',
            'sort_value' => 32,
            'name' => 'Global',
            'display_name' => $this->makeDisplayName($this->__('Global')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global',
            'ipath' => '/1/2/32',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 33,
            'parent_id' => 32,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => '',
            'sort_value' => 33,
            'name' => 'Blogging',
            'display_name' => $this->makeDisplayName($this->__('Blogging')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global/Blogging',
            'ipath' => '/1/2/32/33',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 34,
            'parent_id' => 32,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => '',
            'sort_value' => 34,
            'name' => 'Music and audio',
            'display_name' => $this->makeDisplayName($this->__('Music and audio')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global/MusicAndAudio',
            'ipath' => '/1/2/32/34',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 35,
            'parent_id' => 32,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => '',
            'sort_value' => 35,
            'name' => 'Art and photography',
            'display_name' => $this->makeDisplayName($this->__('Art and photography')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global/ArtAndPhotography',
            'ipath' => '/1/2/32/35',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 36,
            'parent_id' => 32,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => '',
            'sort_value' => 36,
            'name' => 'Writing and thinking',
            'display_name' => $this->makeDisplayName($this->__('Writing and thinking')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global/WritingAndThinking',
            'ipath' => '/1/2/32/36',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 37,
            'parent_id' => 32,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => '',
            'sort_value' => 37,
            'name' => 'Communications and media',
            'display_name' => $this->makeDisplayName($this->__('Communications and media')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global/CommunicationsAndMedia',
            'ipath' => '/1/2/32/37',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 38,
            'parent_id' => 32,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => '',
            'sort_value' => 38,
            'name' => 'Travel and culture',
            'display_name' => $this->makeDisplayName($this->__('Travel and culture')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global/TravelAndCulture',
            'ipath' => '/1/2/32/38',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 39,
            'parent_id' => 32,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => '',
            'sort_value' => 39,
            'name' => 'Science and technology',
            'display_name' => $this->makeDisplayName($this->__('Science and technology')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global/ScienceAndTechnology',
            'ipath' => '/1/2/32/39',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 40,
            'parent_id' => 32,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => '',
            'sort_value' => 40,
            'name' => 'Sport and activities',
            'display_name' => $this->makeDisplayName($this->__('Sport and activities')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global/SportAndActivities',
            'ipath' => '/1/2/32/40',
            'status' => 'A'
        ];
        $objArray[] = [
            'id' => 41,
            'parent_id' => 32,
            'is_locked' => 0,
            'is_leaf' => 1,
            'value' => '',
            'sort_value' => 41,
            'name' => 'Business and work',
            'display_name' => $this->makeDisplayName($this->__('Business and work')),
            'display_desc' => $this->makeDisplayDesc(),
            'path' => '/__SYSTEM__/Modules/Global/BusinessAndWork',
            'ipath' => '/1/2/32/41',
            'status' => 'A'
        ];

        foreach ($objArray as $obj) {
            $category = new CategoryEntity();

            // we need to force the ID to be set here - drak
            // it just means we can work with the array dataset above.
            $metadata = $this->entityManager->getClassMetaData(get_class($category));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_NONE);

            if ($obj['parent_id'] == 0) {
                $obj['parent'] = null;
            } else {
                $obj['parent'] = $this->entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $obj['parent_id']);
            }
            unset($obj['parent_id']);

            if (isset($obj['__ATTRIBUTES__'])) {
                $attributes = $obj['__ATTRIBUTES__'];
                unset($obj['__ATTRIBUTES__']);
            }

            $category->merge($obj);
            // see note above about setting these fields during installation
            $category->setCr_uid($adminUserObj);
            $category->setLu_uid($adminUserObj);
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_AUTO);

            if (isset($attributes)) {
                foreach ($attributes as $attrib_key => $attrib_name) {
                    $category->setAttribute($attrib_name, $attrib_key);
                }
            }

            // unset this so it doesn't persist in the next foreach
            unset($attributes);
        }

        $this->entityManager->flush();
    }

    /**
     * convert a display name into a localised array
     *
     * @param string $name the input display name
     *
     * @return array the localised array
     */
    public function makeDisplayName($name)
    {
        return [$this->container->getParameter('locale') => $name];
    }

    /**
     * convert a description into a localised array
     *
     * @param string name the input description
     *
     * @return array the localised array
     */
    public function makeDisplayDesc()
    {
        return [$this->container->getParameter('locale') => ''];
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
            $category = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity')
                ->findOneBy(['id' => $attribute['object_id']]);
            if (isset($category) && is_object($category)) {
                $category->setAttribute($attribute['attribute_name'], $attribute['value']);
            }
        }
        $this->entityManager->flush();
    }
}
