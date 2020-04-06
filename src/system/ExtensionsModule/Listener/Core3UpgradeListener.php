<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\Doctrine\ColumnExistsTrait;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreUpgradePreExtensionUpgrade;

class Core3UpgradeListener implements EventSubscriberInterface
{
    use ColumnExistsTrait;

    public static function getSubscribedEvents()
    {
        return [
            CoreUpgradePreExtensionUpgrade::class => 'upgrade'
        ];
    }

    public function upgrade(CoreUpgradePreExtensionUpgrade $event): void
    {
        if (!version_compare($event->getCurrentCoreVersion(), '3.0.0', '<')) {
            return;
        }
        $sqls = [];
        if (!$this->columnExists('modules', 'coreCompatibility')) {
            $sqls[] = 'ALTER TABLE `modules` CHANGE `core_min` `coreCompatibility` VARCHAR(64) NOT NULL';
            $sqls[] = 'ALTER TABLE `modules` DROP COLUMN `core_max`';
        }
        $sm = $this->conn->getSchemaManager();
        if ($sm->tablesExist(['modules'])) {
            $sqls[] = 'ALTER TABLE `modules` RENAME TO `extensions`';
            $sqls[] = 'ALTER TABLE `extensions` ADD COLUMN `icon` VARCHAR(50) NOT NULL AFTER `description`';
        }
        foreach ($sqls as $sql) {
            $this->conn->executeQuery($sql);
        }
    }
}
