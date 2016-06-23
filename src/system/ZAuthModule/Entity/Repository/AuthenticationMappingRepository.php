<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

class AuthenticationMappingRepository extends EntityRepository implements AuthenticationMappingRepositoryInterface
{
    public function persistAndFlush(AuthenticationMappingEntity $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    public function removeByZikulaId($uid)
    {
        $mapping = parent::findOneBy(['uid' => $uid]);
        if (isset($mapping)) {
            $this->_em->remove($mapping);
            $this->_em->flush();
        }
    }
}
