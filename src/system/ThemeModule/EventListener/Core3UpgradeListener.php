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
        $sm = $this->conn->getSchemaManager();
        if ($sm->tablesExist(['themes'])) {
            $sql = 'DROP TABLE `themes`';
            $this->conn->executeQuery($sql);
        }
    }
}
