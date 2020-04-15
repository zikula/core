<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\Doctrine\ColumnExistsTrait;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\CharsetRecodeHelper;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreUpgradePreExtensionUpgrade;

class Core3UpgradeListener implements EventSubscriberInterface
{
    use ColumnExistsTrait;

    /**
     * @var CharsetRecodeHelper
     */
    private $charsetRecodeHelper;

    public function __construct(
        CharsetRecodeHelper $charsetRecodeHelper
    ) {
        $this->charsetRecodeHelper = $charsetRecodeHelper;
    }

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
        if (!$this->columnExists('bundles', 'bundlestate')) {
            return;
        }
        $commands = [];
        $commands[] = 'ALTER TABLE `bundles` DROP COLUMN `bundlestate`';
        $commands = array_merge($commands, $this->charsetRecodeHelper->getCommands());
        foreach ($commands as $sql) {
            $this->conn->executeQuery($sql);
        }
    }
}
