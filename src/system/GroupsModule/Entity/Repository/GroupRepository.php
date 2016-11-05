<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class GroupRepository extends EntityRepository implements GroupRepositoryInterface, ObjectRepository, Selectable
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string $indexField
     * @return array
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findAllAndIndexBy($indexField)
    {
        return $this->createQueryBuilder('g')
            ->select('g')
            ->indexBy('g', 'g.' . $indexField)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param bool $includeAll
     * @param bool $includeUnregistered
     * @return array
     */
    public function getGroupNamesById($includeAll = true, $includeUnregistered = true)
    {
        $groups = [];
        $groups[PermissionApi::ALL_GROUPS] = $this->translator->__('All groups');
        $groups[PermissionApi::UNREGISTERED_USER_GROUP] = $this->translator->__('Unregistered');

        $entities = parent::findAll();
        foreach ($entities as $group) {
            $groups[$group->getGid()] = $group->getName();
        }

        return $groups;
    }
}
