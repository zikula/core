<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class ExampleDoctrine_Installer extends Zikula_Installer
{

    /**
     * install the ExampleDoctrine module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return       bool       true on success, false otherwise
     */
    public function install()
    {
        // create the socialNetwork table
        try {
            DoctrineUtil::createTablesFromModels('ExampleDoctrine');
        } catch (Exception $e) {
            return false;
        }

        $this->defaultdata();

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * This function can be called multiple times
     *
     * @param       int        $oldversion version to upgrade from
     * @return      bool       true on success, false otherwise
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion)
        {
            case 0.5:
            // do something
            case 1.0:
            // do something
            // DoctrineUtil::*() for adding/dropping columns/index and so on
            // last do DoctrineUtil::createTablesFromModels('ExampleDoctrine');
            // to create any new tables
        }

        // Update successful
        return true;
    }

    /**
     * delete the module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return       bool       true on success, false otherwise
     */
    public function uninstall()
    {
        // drop table
        DoctrineUtil::dropTable('exampledoctrine_users');

        // remove all module vars
        ModUtil::delVar('ExampleDoctrine');

        // Deletion successful
        return true;
    }

    protected function defaultdata()
    {
        $user = new ExampleDoctrine_Model_User();
        $user->username = 'drak';
        $user->password = 'guessme';
        // could also use $user->merge($arrayObj); where $arrayObj is index array of field => value.
        $user->save();
    }
}