<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * MySqlGenerateSchemaListener
 */
class SystemPlugin_Doctrine_MySqlGenerateSchemaListener
{
    const postGenerateSchemaTable = 'postGenerateSchema';
    
    public function __construct(\Doctrine\Common\EventManager $evm)
    {
        $evm->addEventListener(array(self::postGenerateSchemaTable), $this);
    }
    
    public function postGenerateSchema(\Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs $event) 
    {
        foreach($event->getSchema()->getTables() as $table) {
            $table->addOption('engine', $GLOBALS['ZConfig']['DBInfo']['databases']['default']['dbtabletype']);
            $table->addOption('charset', $GLOBALS['ZConfig']['DBInfo']['databases']['default']['charset']);
            $table->addOption('collate', $GLOBALS['ZConfig']['DBInfo']['databases']['default']['collate']);
        }
    }
}


