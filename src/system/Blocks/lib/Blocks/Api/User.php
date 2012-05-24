<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
/**
 * Blocks_Api_User class.
 */
class Blocks_Api_User extends Zikula_AbstractApi
{
    /**
     * Get all blocks.
     *
     * This function gets all block entries from the database.
     *
     * @param 'blockposition_id'    block position id to filter block selection for.
     * @param 'module_id'           module id to filter block selection for.
     * @param 'language'            language to filter block selection for.
     * @param 'active_status'       filter by active status (0=all, 1=active, 2=inactive).
     *
     * @return array array of items, or false on failure.
     */
    public function getall($args)
    {
        // create an empty items array
        $items = array();

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_OVERVIEW)) {
            return $items;
        }

        // backwards compatibility
        if (isset($args['modid']) && !isset($args['module_id'])) {
            $args['module_id'] = $args['modid'];
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('b')
           ->from('Blocks_Entity_Block', 'b');

        // add clause for filtering blockposition
        if (isset($args['blockposition_id']) && is_numeric($args['blockposition_id']) && $args['blockposition_id']) {
            $entity = $this->name . '_Entity_BlockPlacement';
            $blockitems = $this->entityManager->getRepository($entity)->findBy(array('pid' => $args['blockposition_id']));

            $bidList = array(0);
            foreach ($blockitems as $blockitem) {
                $bidList[] = $blockitem['bid'];
            }

            $qb->andWhere($qb->expr()->in('b.bid', $bidList));
        }

        // add clause for filtering module
        if (isset($args['module_id']) && is_numeric($args['module_id']) && $args['module_id']) {
            $qb->andWhere($qb->expr()->eq('b.mid', $qb->expr()->literal($args['module_id'])));
        }

        // add clause for filtering language
        if (isset($args['language']) && $args['language']) {
            $qb->andWhere($qb->expr()->eq('b.language', $qb->expr()->literal($args['language'])));
        }

        // add clause for filtering status
        if (isset($args['active_status']) && is_numeric($args['active_status']) && $args['active_status']) {
            if ($args['active_status'] == 1) {
                $active = 1;
            } else {
                 $active = 0;
            }

            $qb->andWhere($qb->expr()->eq('b.active', $qb->expr()->literal($active)));
        }

        // add clause for ordering
        $sort = (isset($args['sort']) && $args['sort']) ? 'b.' . $args['sort'] : 'b.title';
        $sortdir = (isset($args['sortdir']) && $args['sortdir']) ? $args['sortdir'] : 'ASC';
        $qb->addOrderBy($sort, $sortdir);

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        //echo $query->getSQL();

        // execute query
        $items = $query->getResult();

        return $items;
    }

    /**
     * get a specific block
     *
     * @param        $args['bid'] id of block to get
     * @return array item array, or false on failure
     */
    public function get($args)
    {
        // Argument check
        if (!isset($args['bid']) || !is_numeric($args['bid'])) {
            return LogUtil::registerArgsError();
        }

        // Return the item array
        $entity = $this->name . '_Entity_Block';
        $item = $this->entityManager->getRepository($entity)->findOneBy(array('bid' => $args['bid']));

        return $item;
    }

    /**
     * utility function to count the number of items held by this module
     *
     * @return integer number of items held by this module
     */
    public function countitems()
    {
        $entity = $this->name . '_Entity_Block';
        $dql = "SELECT count(b.bid) FROM $entity b";
        $query = $this->entityManager->createQuery($dql);
        $numitems = $query->getSingleScalarResult();

        return $numitems;
    }

    /**
     * Get all block positions.
     *
     * This function gets all block position entries from the database.
     *
     * @return array array of items, or false on failure.
     */
    public function getallpositions()
    {
        // create an empty items array
        static $block_positions = array();

        // Security check
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_OVERVIEW)) {
            return $block_positions;
        }

        if (empty($block_positions)) {

            $entity = $this->name . '_Entity_BlockPosition';
            $items = $this->entityManager->getRepository($entity)->findBy(array(), array('name' => 'ASC'));

            foreach ($items as $item) {
                $block_positions[$item['name']] = $item;
            }
        }

        return $block_positions;
    }

    /**
     * Get all block placements.
     *
     * This function gets all block placements entries from the database.
     *
     * @return array array of items, or false on failure.
     */
    public function getallplacements()
    {
        $entity = $this->name . '_Entity_BlockPlacement';
        $items = $this->entityManager->getRepository($entity)->findBy(array(), array('sortorder' => 'ASC'));

        return $items;
    }

    /**
     * Get a specific block position.
     *
     * @param int $args['pid'] position id.
     *
     * @return mixed item array, or false on failure.
     */
    public function getposition($args)
    {
        // Argument check
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            return LogUtil::registerArgsError();
        }

        // Return the item array
        $entity = $this->name . '_Entity_BlockPosition';
        $item = $this->entityManager->getRepository($entity)->findOneBy(array('pid' => $args['pid']));

        return $item;
    }

    /**
     * Get all blocks that are placed in a position
     *
     * @param int $args['pid'] position id.
     *
     * @return mixed item array, or false on failure.
     */
    public function getblocksinposition($args)
    {
        // Argument check
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            return LogUtil::registerArgsError();
        }

        $entity = $this->name . '_Entity_BlockPlacement';
        $items = $this->entityManager->getRepository($entity)->findBy(array('pid' => $args['pid']), array('sortorder' => 'ASC'));

        return $items;
    }

    /**
     * Get all placements of a block
     *
     * @param int $args['bid'] block id.
     *
     * @return mixed item array, or false on failure.
     */
    public function getallblockspositions($args)
    {
        // Argument check
        if (!isset($args['bid']) || !is_numeric($args['bid'])) {
            return LogUtil::registerArgsError();
        }

        $entity = $this->name . '_Entity_BlockPlacement';
        $items = $this->entityManager->getRepository($entity)->findBy(array('bid' => $args['bid']), array('sortorder' => 'ASC'));

        return $items;
    }

    /**
     * Common method for decoding url from bracket notation.
     *
     * @param strign url String to decode.
     *
     * @return string Decoded url.
     */
    public function encodebracketurl($url)
    {
        // allow a simple portable way to link to the home page of the site
        if (empty($url) || $url == '{homepage}') {
            return htmlspecialchars(System::getHomepageUrl());
        }

        if (!preg_match('#\{(.*)\}#', $url, $matches)) {
            return $url;
        }

        $url = explode(':', $matches[1]);

        $modname = $url[0];
        if (isset($url[1])) {
            $type = $url[1];
        } else {
            // defaults allowed here for usability
            $type = 'user';
        }

        if (isset($url[2])) {
            $func = $url[2];
        } else {
            // defaults allowed here for usability
            $func = 'main';
        }

        $params = array();
        if (isset($url[3])) {
            $urlparts = explode('&', $url[3]);
            foreach ($urlparts as $urlpart) {
                $part = explode('=', $urlpart);
                $params[trim($part[0])] = trim($part[1]);
            }
        }

        return ModUtil::url($modname, $type, $func, $params);
    }
}
