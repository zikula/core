<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace ExampleModule;

/**
 * Installer
 */
class Installer extends \Zikula\Framework\AbstractInstaller
{

    /**
     * Install the ExampleModule module.
     *
     * @return boolean
     */
    public function install()
    {
        // create database schema
        try {
            \DoctrineHelper::createSchema($this->entityManager, array('ExampleModule\Entity\User',
                                                                     'ExampleModule\Entity\UserCategory',
                                                                     'ExampleModule\Entity\UserAttribute',
                                                                     'ExampleModule\Entity\UserMetadata'));
        } catch (\Exception $e) {
            return false;
        }

        $this->defaultcategories();
        $this->defaultdata();

        // Initialisation successful
        return true;
    }

    /**
     * Upgrade the module from an old version.
     *
     * This function may be called multiple times.
     *
     * @param integer $oldversion Version to upgrade from.
     *
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion)
        {
            case 0.5:
                // do something
            case 1.0:
                // do something
                // DoctrineHelper::createSchema($this->entityManager, array('ExampleModule\Entity\User'));
                // to create any new tables
        }

        // Update successful
        return true;
    }

    /**
     * Uninstall the module.
     *
     * @return bool
     */
    public function uninstall()
    {
        // drop table
        \DoctrineHelper::dropSchema($this->entityManager, array('ExampleModule\Entity\User',
                                                                     'ExampleModule\Entity\UserCategory',
                                                                     'ExampleModule\Entity\UserAttribute',
                                                                     'ExampleModule\Entity\UserMetadata'));

        // remove all module vars
        $this->delVars();
        
        // delete categories
        CategoryRegistryUtil::deleteEntry('ExampleDoctrine');
        CategoryUtil::deleteCategoriesByPath('/__SYSTEM__/Modules/ExampleDoctrine', 'path');

        return true;
    }
    
    
    /**
     * Provide default categories.
     *
     * @return void
     */
    protected function defaultcategories()
    {

        if (!$cat = CategoryUtil::createCategory('/__SYSTEM__/Modules', 'ExampleDoctrine', null, $this->__('ExampleDoctrine'), $this->__('ExampleDoctrine categories'))) {
            return false;
        }
        
        $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/ExampleDoctrine');
        CategoryRegistryUtil::insertEntry('ExampleDoctrine', 'User', 'Main', $rootcat['id']);

        CategoryUtil::createCategory('/__SYSTEM__/Modules/ExampleDoctrine', 'category1', null, $this->__('Category 1'), $this->__('Category 1'));
    }


    /**
     * Provide default data.
     */
    protected function defaultdata()
    {
        $user = new \ExampleModule\Entity\User();
        $user->setUser('drak', 'guessme');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}