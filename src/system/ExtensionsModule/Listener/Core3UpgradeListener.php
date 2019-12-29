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

use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;

class Core3UpgradeListener implements EventSubscriberInterface
{
    /**
     * @var Connection
     */
    private $conn;

    public function __construct(
    ) {
    }

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
        if ($this->columnExists('modules', 'coreCompatibility')) {
            return;
        }
        $commands = [];
        $commands[] = 'ALTER TABLE `modules` CHANGE `core_min` `coreCompatibility` VARCHAR(64) NOT NULL';
        $commands[] = 'ALTER TABLE `modules` DROP COLUMN `core_max`';
        foreach ($commands as $sql) {
            $this->conn->executeQuery($sql);
        }
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $sm = $this->conn->getSchemaManager();
        $existingColumns = $sm->listTableColumns($tableName);
        foreach ($existingColumns as $existingColumn) {
            if ($existingColumn->getName() === $columnName) {
                return true;
            }
        }

        return false;
    }
}
