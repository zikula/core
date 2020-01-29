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
use Zikula\Bundle\CoreBundle\CoreEvents;
use Zikula\Bundle\CoreBundle\Doctrine\ColumnExistsTrait;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;

class Core3UpgradeListener implements EventSubscriberInterface
{
    use ColumnExistsTrait;

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::CORE_UPGRADE_PRE_MODULE => 'upgrade'
        ];
    }

    public function upgrade(GenericEvent $event): void
    {
        if (!version_compare($event->getArgument('currentVersion'), '3.0.0', '<')) {
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
        }
        foreach ($sqls as $sql) {
            $this->conn->executeQuery($sql);
        }
    }
}
