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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Common\ColumnExistsTrait;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;

class Core3UpgradeListener implements EventSubscriberInterface
{
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
        $sql = 'ALTER TABLE `themes` DROP COLUMN `xhtml`';
        $this->conn->executeQuery($sql);
    }
}
