<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\BlocksModule\Api;

use SecurityUtil;
use System;
use ModUtil;

/**
 * API functions used by user controllers
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * Get all blocks.
     *
     * This function gets all block entries from the database.
     *
     * @param mixed[] $args {<ul>
     *      <li>@type  int     blockposition_id    block position id to filter block selection for.
     *      <li>@type  int     module_id           module id to filter block selection for.
     *      <li>@type  string  language            language to filter block selection for.
     *      <li>@type  int     active_status       filter by active status (0=all, 1=active, 2=inactive).
     *                       </ul>}
     *
     * @return array|bool array of items, or false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getall($args)
    {
        // create an empty items array
        $items = array();

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_OVERVIEW)) {
            return $items;
        }

        // backwards compatibility
        if (isset($args['modid']) && !isset($args['module_id'])) {
            $args['module_id'] = $args['modid'];
        }

        // Argument check
        if (isset($args['blockposition_id']) && !is_numeric($args['blockposition_id']) ||
            isset($args['module_id']) && !is_numeric($args['module_id']) ||
            isset($args['active_status']) && !is_numeric($args['active_status'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('b')
           ->from('Zikula\Module\BlocksModule\Entity\BlockEntity', 'b');

        // add clause for filtering blockposition
        if (isset($args['blockposition_id']) && is_numeric($args['blockposition_id']) && $args['blockposition_id']) {
            $entity = 'Zikula\Module\BlocksModule\Entity\BlockPlacementEntity';
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
     * @param int[] $args {<ul>
     *      <li>@type        $args['bid'] id of block to get
     *                     </ul>}
     *
     * @return array|bool item array, or false on failure
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function get($args)
    {
        // Argument check
        if (!isset($args['bid']) || !is_numeric($args['bid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Return the item array
        $entity = 'Zikula\Module\BlocksModule\\Entity\BlockEntity';
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
        $query = $this->entityManager->createQueryBuilder()
                                     ->select('count(b.bid)')
                                     ->from('Zikula\Module\BlocksModule\Entity\BlockEntity', 'b')
                                     ->getQuery();

        return (int)$query->getSingleScalarResult();;
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
        if (!SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_OVERVIEW)) {
            return $block_positions;
        }

        if (empty($block_positions)) {

            $entity = 'Zikula\Module\BlocksModule\Entity\BlockPositionEntity';
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
        $entity = 'Zikula\Module\BlocksModule\Entity\BlockPlacementEntity';
        $items = $this->entityManager->getRepository($entity)->findBy(array(), array('sortorder' => 'ASC'));

        return $items;
    }

    /**
     * Get a specific block position.
     *
     * @param int[] $args {<ul>
     *      <li>@type int $args['pid'] position id.
     *                     </ul>}
     *
     * @return array|bool item array, or false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getposition($args)
    {
        // Argument check
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Return the item array
        $entity = 'Zikula\Module\BlocksModule\Entity\BlockPositionEntity';
        $item = $this->entityManager->getRepository($entity)->findOneBy(array('pid' => $args['pid']));

        return $item;
    }

    /**
     * Get all blocks that are placed in a position
     *
     * @param int[] $args {<ul>
     *      <li>@type int $args['pid'] position id.
     *                     </ul>}
     *
     * @return array|bool item array, or false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getblocksinposition($args)
    {
        // Argument check
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $entity = 'Zikula\Module\BlocksModule\Entity\BlockPlacementEntity';
        $items = $this->entityManager->getRepository($entity)->findBy(array('pid' => $args['pid']), array('sortorder' => 'ASC'));

        return $items;
    }

    /**
     * Get all placements of a block
     *
     * @param int[] $args {<ul>
     *      <li>@type int $args['bid'] block id.
     *                     </ul>}
     *
     * @return array|bool item array, or false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getallblockspositions($args)
    {
        // Argument check
        if (!isset($args['bid']) || !is_numeric($args['bid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $entity = 'Zikula\Module\BlocksModule\Entity\BlockPlacementEntity';
        $items = $this->entityManager->getRepository($entity)->findBy(array('bid' => $args['bid']), array('sortorder' => 'ASC'));

        return $items;
    }

    /**
     * Common method for decoding url from bracket notation.
     *
     * @param string $url the input url
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
            $func = 'index';
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