<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\SearchModule\Entity\SearchStatEntity;

interface SearchStatRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Returns amount of previous search queries.
     *
     * @return integer
     */
    public function countStats();

    /**
     * Returns stats for given arguments.
     *
     * @param array   $filters Optional array with filters
     * @param array   $sorting Optional array with sorting criteria
     * @param integer $limit   Optional limitation for amount of retrieved objects
     * @param integer $offset  Optional start offset of retrieved objects
     *
     * @return array
     */
    public function getStats($filters = [], $sorting = [], $limit = 0, $offset = 0);

    /**
     * Persist and flush an entity.
     * @param SearchStatEntity $entity
     * @return mixed
     */
    public function persistAndFlush(SearchStatEntity $entity);
}
