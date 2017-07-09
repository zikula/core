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

use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserAttributeEntity;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * Class MigrationHelper
 */
class MigrationHelper
{
    const BATCH_LIMIT = 25;

    /**
     * @var object
     */
    private $conn;

    /**
     * @var ObjectManager
     */
    private $manager;

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
     * @param RegistryInterface $registry
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     */
    public function __construct(
        RegistryInterface $registry,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->conn = $registry->getConnection();
        $this->manager = $registry->getManager();
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @param array $user
     * @param string $method
     * @return AuthenticationMappingEntity|null
     * @throws \Exception
     */
    public function createMappingFromUser($user, $method = ZAuthConstant::AUTHENTICATION_METHOD_EITHER)
    {
        $mapping = new AuthenticationMappingEntity();
        $mapping->setUid($user['uid']);
        $mapping->setUname($user['uname']);
        $mapping->setEmail($user['email']);
        $mapping->setVerifiedEmail(true);
        $mapping->setPass($user['pass']); // previously salted and hashed
        $mapping->setMethod($method);
        $errors = $this->validator->validate($mapping);
        if ($errors->count() > 0) {
            foreach ($errors as $error) {
                $this->logger->addError('Unable to migrate user (' . $user['uname'] . '/' . $user['email'] . ') because: ' . $error->getMessage());
            }

            return null;
        }

        return $mapping;
    }

    /**
     * @param $uid
     * @param $limit
     * @return array
     */
    private function getUnMigratedUsers($uid, $limit)
    {
        $sql = $this->conn->createQueryBuilder()
            ->select()
            ->from('users', 'u')
            ->where('u.uid > ?')
            ->setParameter(0, $uid)
            ->andWhere("u.pass != ''")
            ->orderBy('u.uid', 'ASC')
            ->setMaxResults($limit)
            ->getSQL();

        return $this->conn->prepare($sql)->fetchAll();
    }

    public function getMaxUnMigratedUid()
    {
        $sql = $this->conn->createQueryBuilder()
            ->select('MAX(u.uid) as max')
            ->from('users', 'u')
            ->where("u.pass != ''");

        return $this->conn->prepare($sql)->fetchColumn();
    }

    public function migrateUsers($lastUid)
    {
        $users = $this->getUnMigratedUsers($lastUid, self::BATCH_LIMIT);
        $complete = 0;
        foreach ($users as $user) {
            $mapping = $this->createMappingFromUser($user);
            if ($mapping) {
                $this->manager->persist($mapping);
                $attribute = new UserAttributeEntity($user['uid'], UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $mapping->getMethod());
                $this->manager->persist($attribute);
                $complete++;
            }
            $lastUid = $user->getUid();
        }
        $this->manager->flush();

        return ['lastUid' => $lastUid, 'complete' => $complete];
    }
}
