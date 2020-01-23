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

namespace Zikula\AdminModule\Listener;

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
        if ($this->columnExists('admin_category', 'icon')) {
            return;
        }
        $sql = 'ALTER TABLE `admin_category` ADD COLUMN `icon` VARCHAR(50) NOT NULL AFTER `description`';
        $this->conn->executeQuery($sql);
    }
}
