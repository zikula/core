<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity;

class ExtensionDependencyRepository extends EntityRepository
{
    public function reloadExtensionDependencies($extensionsFromFile)
    {
        // truncate the table
        $connection = $this->_em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
        $connection->executeUpdate($platform->getTruncateTableSQL('module_deps'));
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');

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
