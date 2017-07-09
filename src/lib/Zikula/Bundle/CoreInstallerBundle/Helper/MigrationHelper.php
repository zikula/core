<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

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
 * Class MigrationHelper
 */
class MigrationHelper
{
    const BATCH_LIMIT = 25;

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
     * @param array $criteria
     * @param string $method
     * @return AuthenticationMappingEntity
     */
    public function createMappingFromUserCriteria(array $criteria, $method = ZAuthConstant::AUTHENTICATION_METHOD_EITHER)
    {
        $userEntity = $this->userRepository->findOneBy($criteria);
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

    /**
     * @param $uid
     * @param $limit
     * @return UserEntity[]
     */
    public function getUnMigratedUsers($uid, $limit)
    {
        return $this->entityManager->createQueryBuilder()
            ->select(UserEntity::class, 'u')
            ->where('u.uid > :uid')
            ->setParameter('uid', $uid)
            ->andWhere("u.pass != ''")
            ->orderBy('u.uid', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getMaxUnMigratedUid()
    {
        return $this->entityManager->createQueryBuilder()
            ->select('MAX(u.uid)')
            ->from(UserEntity::class, 'u')
            ->where("u.pass != ''")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function migrateUsers($lastUid)
    {
        $userEntities = $this->getUnMigratedUsers($lastUid, self::BATCH_LIMIT);
        $complete = 0;
        foreach ($userEntities as $userEntity) {
            $mapping = $this->createMappingFromUser($userEntity);
            if ($mapping) {
                $this->entityManager->persist($mapping);
                $userEntity->setPass('');
                $userEntity->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $mapping->getMethod());
                $complete++;
            }
            $lastUid = $userEntity->getUid();
        }
        $this->entityManager->flush();

        return ['lastUid' => $lastUid, 'complete' => $complete];
    }
}
