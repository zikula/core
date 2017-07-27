<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * @deprecated remove at Core-2.0
 * Class MigrationHelper
 */
class MigrationHelper
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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * MigrationHelper constructor.
     * @param UserRepositoryInterface $userRepository
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @deprecated
     * @param UserEntity $userEntity
     * @param string $method
     * @return AuthenticationMappingEntity|null
     * @throws \Exception
     */
    public function createMappingFromUser(UserEntity $userEntity, $method = ZAuthConstant::AUTHENTICATION_METHOD_EITHER)
    {
        $mapping = new AuthenticationMappingEntity();
        $mapping->setUid($userEntity->getUid());
        $mapping->setUname($userEntity->getUname());
        $mapping->setEmail($userEntity->getEmail());
        $mapping->setVerifiedEmail(true);
        $mapping->setPass($userEntity->getPass()); // previously salted and hashed
        $mapping->setMethod($method);
        $errors = $this->validator->validate($mapping);
        if ($errors->count() > 0) {
            foreach ($errors as $error) {
                $this->logger->addError('Unable to migrate user (' . $userEntity->getUname() . '/' . $userEntity->getEmail() . ') because: ' . $error->getMessage());
            }

            return null;
        }

        return $mapping;
    }

    /**
     * @deprecated
     * @param array $criteria
     * @param string $method
     * @return AuthenticationMappingEntity
     */
    public function createMappingFromUserCriteria(array $criteria, $method = ZAuthConstant::AUTHENTICATION_METHOD_EITHER)
    {
        /** @var UserEntity $userEntity */
        $userEntity = $this->userRepository->findOneBy($criteria);
        if ($userEntity->hasAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY)) {
            // user has been migrated or is registered via another authentication method.
            return null;
        }
        $mapping = isset($userEntity) ? $this->createMappingFromUser($userEntity, $method) : null;
        if (isset($mapping)) {
            $this->entityManager->persist($mapping);
            // remove data from UserEntity
            $userEntity->setPass('');
            $userEntity->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $mapping->getMethod());
            $this->entityManager->flush();
        }

        return $mapping;
    }
}
