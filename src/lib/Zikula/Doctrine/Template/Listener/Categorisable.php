<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Doctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Doctrine listener for the Categorisable doctrine template.
 */
class Zikula_Doctrine_Template_Listener_Categorisable extends Doctrine_Record_Listener
{
    /**
     * Adds categories relation specific joins.
     *
     * @param Doctrine_Event $event Event.
     *
     * @return void
     */
    public function preDqlSelect(Doctrine_Event $event)
    {
        $query = $event->getQuery();
        $params = $event->getParams();

        $dql = $params['alias'].'.Categories catmobj INDEXBY catmobj.reg_property';

        if (!$query->contains($dql)) {
            $query->leftJoin($dql)
                  ->leftJoin('catmobj.Category cat');
        }
    }
}
