<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Helper\AccessHelper;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\ZAuthConstant;

class SuperUserHelper
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ParameterHelper
     */
    private $parameterHelper;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var AccessHelper
     */
    private $accessHelper;

    /**
     * @var PasswordApiInterface
     */
    private $passwordApi;

    public function __construct(
        UserRepositoryInterface $userRepository,
        EntityManagerInterface $entityManager,
        ParameterHelper $parameterHelper,
        RequestStack $requestStack,
        AccessHelper $accessHelper,
        PasswordApiInterface $passwordApi
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->parameterHelper = $parameterHelper;
        $this->requestStack = $requestStack;
        $this->accessHelper = $accessHelper;
        $this->passwordApi = $passwordApi;
    }

    /**
     * This inserts the admin's user data
     */
    public function updateAdmin(): bool
    {
        $params = $this->parameterHelper->decodeParameters($this->parameterHelper->getYamlHelper()->getParameters());
        /** @var UserEntity $userEntity */
        $userEntity = $this->userRepository->find(2);
        $userEntity->setUname($params['username']);
        $userEntity->setEmail($params['email']);
        $userEntity->setActivated(1);
        $userEntity->setUser_Regdate(new DateTime());
        $userEntity->setLastlogin(new DateTime());
        $this->entityManager->persist($userEntity);

        $mapping = new AuthenticationMappingEntity();
        $mapping->setUid($userEntity->getUid());
        $mapping->setUname($userEntity->getUname());
        $mapping->setEmail($userEntity->getEmail());
        $mapping->setVerifiedEmail(true);
        $mapping->setPass($this->passwordApi->getHashedPassword($params['password']));
        $mapping->setMethod(ZAuthConstant::AUTHENTICATION_METHOD_UNAME);
        $this->entityManager->persist($mapping);

        $this->entityManager->flush();

        return true;
    }

    public function loginAdmin(): bool
    {
        $params = $this->parameterHelper->decodeParameters($this->parameterHelper->getYamlHelper()->getParameters());
        $user = $this->userRepository->findOneBy(['uname' => $params['username']]);
        $request = $this->requestStack->getCurrentRequest();
        if (isset($request) && $request->hasSession()) {
            $this->accessHelper->login($user, true);
        }

        return true;
    }
}
