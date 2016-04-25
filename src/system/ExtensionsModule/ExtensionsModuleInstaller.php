<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule;

use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Zikula\Core\AbstractExtensionInstaller;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

/**
 * Installation and upgrade routines for the extensions module
 */
class ExtensionsModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Install the Extensions module.
     *
     * @return boolean true if installation is successful, false otherwise
     */
    public function install()
    {
        // create tables
        $entities = array(
            'Zikula\ExtensionsModule\Entity\ExtensionEntity',
            'Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity',
            'Zikula\ExtensionsModule\Entity\ExtensionVarEntity',
        );

        try {
            $this->schemaTool->create($entities);
        } catch (\Exception $e) {
            return false;
        }

        // populate default data
        $this->defaultData();
        $this->setVar('itemsperpage', 40);

        // Initialisation successful
        return true;
    }

    /**
     * Upgrade the module from an old version.
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param string $oldversion Version number string to upgrade from.
     *
     * @return  boolean|string True on success, last valid version string or false if fails.
     */
    public function upgrade($oldVersion)
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '3.7.10':
                // Load DB connection
                $connection = $this->entityManager->getConnection();

                // increase length of some hook table fields from 20 to 60
                $commands = array();
                $commands[] = "ALTER TABLE `hook_provider` CHANGE `method` `method` VARCHAR(60) NOT NULL";
                $commands[] = "ALTER TABLE `hook_runtime` CHANGE `method` `method` VARCHAR(60) NOT NULL";

                foreach ($commands as $sql) {
                    $stmt = $connection->executeQuery($sql);
                }
            case '3.7.11':
                $this->schemaTool->update(['Zikula\ExtensionsModule\Entity\ExtensionEntity']);
            case '3.7.12':
                $this->setVar('itemsperpage', 40);
            case '3.7.13':
                // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the modules module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     *
     * Since the modules module should never be deleted we'all always return false here
     * @return boolean false this module cannot be deleted
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Create the default data for the Extensions module.
     *
     * @return void
     */
    public function defaultData()
    {
        $scanner = new Scanner();
        $jsonPath = realpath(__DIR__ . '/composer.json');
        $jsonContent = $scanner->decode($jsonPath);
        $metaData = new MetaData($jsonContent);
        if (!empty($this->container)) {
            $metaData->setTranslator($this->container->get('translator'));
        }
        $meta = $metaData->getFilteredVersionInfoArray();
        $meta['state'] = ExtensionApi::STATE_ACTIVE;
        unset($meta['dependencies']);
        unset($meta['oldnames']);

        $entity = new ExtensionEntity();
        $entity->merge($meta);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
