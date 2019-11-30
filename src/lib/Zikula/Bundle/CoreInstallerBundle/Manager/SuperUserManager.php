<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Manager;

use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Api\PasswordApi;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\ZAuthConstant;

class SuperUserManager
{
    /**
     * This function inserts the admin's user data
     */
    private function updateAdmin(): bool
    {
        $entityManager = $this->container->get('doctrine')->getManager();
        $params = $this->decodeParameters($this->yamlManager->getParameters());
        /** @var UserEntity $userEntity */
        $userEntity = $entityManager->find('ZikulaUsersModule:UserEntity', 2);
        $userEntity->setUname($params['username']);
        $userEntity->setEmail($params['email']);
        $userEntity->setActivated(1);
        $userEntity->setUser_Regdate(new DateTime());
        $userEntity->setLastlogin(new DateTime());
        $entityManager->persist($userEntity);

        $mapping = new AuthenticationMappingEntity();
        $mapping->setUid($userEntity->getUid());
        $mapping->setUname($userEntity->getUname());
        $mapping->setEmail($userEntity->getEmail());
        $mapping->setVerifiedEmail(true);
        $mapping->setPass($this->container->get(PasswordApi::class)->getHashedPassword($params['password']));
        $mapping->setMethod(ZAuthConstant::AUTHENTICATION_METHOD_UNAME);
        $entityManager->persist($mapping);

        $entityManager->flush();

        return true;
    }
}
