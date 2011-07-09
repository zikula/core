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
 * StorageEngineListener
 */
class SystemPlugin_Doctrine_MySqlStorageEngineListener
{
    const postGenerateSchemaTable = 'postGenerateSchemaTable';
    
    public function __construct(\Doctrine\Common\EventManager $evm)
    {
        $evm->addEventListener(array(self::postGenerateSchemaTable), $this);
    }
    
    public function postGenerateSchemaTable(\Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs $event) 
    {
        $event->getClassTable()->addOption('engine', $GLOBALS['ZConfig']['DBInfo']['databases']['default']['dbtabletype']);
    }
}


