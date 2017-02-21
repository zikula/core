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
use Zikula\SearchModule\Entity\SearchResultEntity;

interface SearchResultRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Returns amount of results.
     *
     * @param string $sessionId Session id to filter results
     *
     * @return integer
     */
    public function countResults($sessionId = '');

    /**
     * Returns results for given arguments.
     *
     * @param array   $filters Optional array with filters
     * @param array   $sorting Optional array with sorting criteria
     * @param integer $limit   Optional limitation for amount of retrieved objects
     * @param integer $offset  Optional start offset of retrieved objects
     *
     * @return array
     */
    public function getResults($filters = [], $sorting = [], $limit = 0, $offset = 0);

    /**
     * Deletes all results for the current session.
     *
     * @param string $sessionId Session id of current user
     *
     * @return void
     */
    public function clearOldResults($sessionId = '');

    /**
     * Persist an entity.
     * @param SearchResultEntity $entity
     * @return mixed
     */
    public function persist(SearchResultEntity $entity);

    /**
     * @param SearchResultEntity|null $entity
     * @return mixed
     */
    public function flush(SearchResultEntity $entity = null);
}
