<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Doctrine listener for the Categorisable doctrine template.
 *
 * @deprecated since 1.4.0
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

        // aliases must be specific to entities
        $aliasCategories = $params['alias'] . 'Catmapobj';
        $dql = $params['alias'].'.Categories '.$aliasCategories.' INDEXBY '.$aliasCategories.'.reg_property';

        if (!$query->contains($dql)) {
            $query->leftJoin($dql)
                  ->leftJoin($aliasCategories.'.Category '.$params['alias'].'Cat');
        }
    }
}
