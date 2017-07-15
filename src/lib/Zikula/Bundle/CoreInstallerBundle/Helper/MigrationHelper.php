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
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * Class MigrationHelper
 */
class MigrationHelper
{
    const BATCH_LIMIT = 25;

    /**
     * @var Connection
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
        $query = $this->conn->createQueryBuilder()
            ->select('*')
            ->from('zauth_authentication_mapping', 'z')
            ->where('z.uid = ?')
            ->setParameter(0, $user['uid'])
            ->setMaxResults(1);
        $mapping = $query->execute()->fetchAll();
        if (count($mapping) > 0) {
            return null;
        }

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
     * @param integer $limit
     * @return array
     */
    private function getUnMigratedUsers($uid, $limit)
    {
        $qb = $this->conn->createQueryBuilder();
        $query = $qb
            ->select('*')
            ->from('users', 'u')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gt('u.uid', '?'),
                    $qb->expr()->neq('u.pass', "''")
                )
            )
            ->setParameter(0, $uid)
            ->orderBy('u.uid', 'ASC')
            ->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }

    public function getMaxUnMigratedUid()
    {
        $query = $this->conn->createQueryBuilder()
            ->select('MAX(u.uid) as max')
            ->from('users', 'u')
            ->where("u.pass != ''");

        return $query->execute()->fetchColumn();
    }

    public function countUnMigratedUsers()
    {
        $query = $this->conn->createQueryBuilder()
            ->select('COUNT(u.uid) as count')
            ->from('users', 'u')
            ->where("u.pass != ''");

        return $query->execute()->fetchColumn();
    }

    public function migrateUsers($lastUid)
    {
        $users = $this->getUnMigratedUsers($lastUid, self::BATCH_LIMIT);
        $complete = 0;
        foreach ($users as $user) {
            $mapping = $this->createMappingFromUser($user);
            if ($mapping) {
                $this->manager->persist($mapping);
                $this->conn->insert('users_attributes', [
                    'user_id' => $user['uid'],
                    'name' => UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY,
                    'value' => $mapping->getMethod()
                ]);
                $this->conn->update('users', ['pass' => ''], ['uid' => $user['uid']]);
                $complete++;
            }
            $lastUid = $user['uid'];
        }
        $this->manager->flush();

        return ['lastUid' => $lastUid, 'complete' => $complete];
    }
}
