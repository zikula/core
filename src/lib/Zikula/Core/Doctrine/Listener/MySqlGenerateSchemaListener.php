<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Doctrine\Listener;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * MySqlGenerateSchemaListener
 */
class MySqlGenerateSchemaListener
{
    const postGenerateSchemaTable = 'postGenerateSchema';

    public function __construct(EventManager $evm)
    {
        $evm->addEventListener([self::postGenerateSchemaTable], $this);
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $event)
    {
        foreach ($event->getSchema()->getTables() as $table) {
            $table->addOption('engine', $GLOBALS['ZConfig']['DBInfo']['databases']['default']['dbtabletype']);
            $table->addOption('charset', $GLOBALS['ZConfig']['DBInfo']['databases']['default']['charset']);
            $table->addOption('collate', $GLOBALS['ZConfig']['DBInfo']['databases']['default']['collate']);
        }
    }
}
