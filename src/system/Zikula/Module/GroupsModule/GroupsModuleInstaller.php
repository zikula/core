<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\GroupsModule;

use DoctrineHelper;

/**
 * Installation and upgrade routines for the groups module
 */
class GroupsModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * initialise the groups module
     *
     * @return bool true if initialisation successful, false otherwise
     */
    public function install()
    {
        // create tables
        $classes = array(
            'Zikula\Module\GroupsModule\Entity\GroupEntity',
            'Zikula\Module\GroupsModule\Entity\GroupMembershipEntity',
            'Zikula\Module\GroupsModule\Entity\GroupApplicationEntity'
        );

        try {
            DoctrineHelper::createSchema($this->entityManager, $classes);
        } catch (\Exception $e) {
            return false;
        }

        // set all our module vars
        $this->setVar('itemsperpage', 25);
        $this->setVar('defaultgroup', 1);
        $this->setVar('mailwarning', 0);
        $this->setVar('hideclosed', 0);

        // Set the primary admin group gid as a module var so it is accessible by other modules,
        // but it should not be editable at this time. For now it is read-only.
        $this->setVar('primaryadmingroup', 2);

        // create the default data for the modules module
        $this->defaultdata();

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * @param string $oldversion version number string to upgrade from
     *
     * @return bool|string true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '2.3.2':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the groups module
     *
     * @return bool false this module cannot be deleted
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }

    /**
     * create the default data for the groups module
     *
     * @return void
     */
    public function defaultdata()
    {
        $records = array(
            array(  'name'        => $this->__('Users'),
                    'description' => $this->__('By default, all users are made members of this group.'),
                    'prefix'      => $this->__('usr')),
            array(  'name'        => $this->__('Administrators'),
                    'description' => $this->__('Group of administrators of this site.'),
                    'prefix'      => $this->__('adm'))
        );

        foreach ($records as $record) {
            $item = new \Zikula\Module\GroupsModule\Entity\GroupEntity;
            $item['name'] = $record['name'];
            $item['description'] = $record['description'];
            $item['prefix'] = $record['prefix'];
            $this->entityManager->persist($item);
        }

        // Insert Anonymous and Admin users
        $records = array(
            // Anonymous user, member of Users group (This is required. Handling of 'unregistered' state for
            // permissions is handled separately.)
            array('gid' => '1',
                  'uid' => '1'),
            // Admin user, member of Users group (Not strictly necessary, but for completeness.)
            array('gid' => '1',
                  'uid' => '2'),
            // Admin user, member of Administrators group
            array('gid' => '2',
                  'uid' => '2')
        );

        foreach ($records as $record) {
            $item = new \Zikula\Module\GroupsModule\Entity\GroupMembershipEntity;
            $item['gid'] = $record['gid'];
            $item['uid'] = $record['uid'];
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }
}