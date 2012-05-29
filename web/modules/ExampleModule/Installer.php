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

        return true;
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