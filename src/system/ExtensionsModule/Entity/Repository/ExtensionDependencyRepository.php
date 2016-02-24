<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity;

class ExtensionDependencyRepository extends EntityRepository
{
    public function reloadExtensionDependencies($extensionsFromFile)
    {
        // truncate the table
        $this->_em->createQuery('DELETE FROM ' . $this->_entityName)->execute();

        foreach ($extensionsFromFile as $name => $extensionFromFile) {
            $extension = $this->_em->getRepository('ZikulaExtensionsModule:ExtensionEntity')->findOneBy(['name' => $name]);
            if (isset($extensionFromFile['dependencies']) && !empty($extensionFromFile['dependencies'])) {
                $dependencies = unserialize($extensionFromFile['dependencies']);
                foreach ($dependencies as $dependency) {
                    $entity = new ExtensionDependencyEntity();
                    $entity->merge($dependency);
                    $entity->setModid($extension->getId());
                    $this->_em->persist($entity);
                }
            }
        }
        $this->_em->flush();
    }
}
