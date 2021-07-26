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

namespace Zikula\ZAuthModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreInstallerBundle\Event\CoreUpgradePreExtensionUpgrade;
use Zikula\ZAuthModule\ZAuthConstant;

class Core3UpgradeListener implements EventSubscriberInterface
{
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
        $sql = "
            UPDATE users_attributes
            SET value='" . ZAuthConstant::AUTHENTICATION_METHOD_EITHER . "'
            WHERE `name` LIKE 'authenticationMethod'
            AND `value` IN ('" . ZAuthConstant::AUTHENTICATION_METHOD_UNAME . "', '" . ZAuthConstant::AUTHENTICATION_METHOD_EMAIL . "')
        ";
        $this->conn->executeQuery($sql);
    }
}
