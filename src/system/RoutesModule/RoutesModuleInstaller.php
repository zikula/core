<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule;

use Zikula\RoutesModule\Base\AbstractRoutesModuleInstaller;

class RoutesModuleInstaller extends AbstractRoutesModuleInstaller
{
    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            case '1.1.0': // shipped with Core-1.4.3
                // rename createdUserId field to createdBy_id
                $sql = '
                    ALTER TABLE `zikula_routes_route`
                    CHANGE `createdUserId` `createdBy_id` int(11) NOT NULL
                ';
                $this->entityManager->getConnection()->exec($sql);

                // rename updatedUserId field to updatedBy_id
                $sql = '
                    ALTER TABLE `zikula_routes_route`
                    CHANGE `updatedUserId` `updatedBy_id` int(11) NOT NULL
                ';
                $this->entityManager->getConnection()->exec($sql);
            case '1.1.1':
                // drop obsolete fields
                $fieldNames = ['routeType', 'replacedRouteName', 'sort_group'];
                foreach ($fieldNames as $fieldName) {
                    $sql = '
                        ALTER TABLE `zikula_routes_route`
                        DROP COLUMN `' . $fieldName . '`
                    ';
                    $this->entityManager->getConnection()->exec($sql);
                }
                // add new field
                $sql = '
                    ALTER TABLE `zikula_routes_route`
                    ADD `options` LONGTEXT NOT NULL
                    COMMENT \'(DC2Type:array)\' AFTER `requirements`
                ';
                $this->entityManager->getConnection()->exec($sql);
                $sql = '
                    UPDATE `zikula_routes_route`
                    SET `options` = \'a:0:{}\';
                ';
                $this->entityManager->getConnection()->exec($sql);
            case '1.1.2': // shipped with Core-2.0.15
                // nothing
        }

        return true;
    }
}
