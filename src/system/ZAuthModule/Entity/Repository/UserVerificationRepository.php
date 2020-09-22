<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Entity\Repository;

use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\Entity\UserVerificationEntity;
use Zikula\ZAuthModule\ZAuthConstant;

class UserVerificationRepository extends ServiceEntityRepository implements UserVerificationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserVerificationEntity::class);
    }

    public function persistAndFlush(UserVerificationEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function removeAndFlush(UserVerificationEntity $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function removeByZikulaId(int $userId): void
    {
        /** @var UserVerificationEntity $entity */
        $entity = $this->findOneBy(['uid' => $userId]);
        if ($entity) {
            $this->removeAndFlush($entity);
        }
    }

    public function purgeExpiredRecords(
        int $daysOld,
        int $changeType = ZAuthConstant::VERIFYCHGTYPE_REGEMAIL,
        bool $deleteUserEntities = true
    ): array {
        if ($daysOld < 1) {
            return [];
        }
        // Expiration date/times, as with all date/times in the Users module, are stored as UTC.
        $staleRecordUTC = new DateTime('now', new DateTimeZone('UTC'));
        $staleRecordUTC->modify("-{$daysOld} days");

        $qb = $this->createQueryBuilder('v');
        $and = $qb->expr()->andX()
            ->add($qb->expr()->eq('v.changetype', ':changeType'))
            ->add($qb->expr()->isNotNull('v.createdDate'))
            ->add($qb->expr()->neq('v.createdDate', ':createdDtNot'))
            ->add($qb->expr()->lt('v.createdDate', ':createdDtMax'));
        $qb->select('v')
            ->where($and)
            ->setParameter('changeType', $changeType)
            ->setParameter('createdDtNot', '0000-00-00 00:00:00')
            ->setParameter('createdDtMax', $staleRecordUTC);
        $staleVerificationRecords = $qb->getQuery()->getResult();

        $deletedUsers = [];
        $userRepo = $this->_em->getRepository(UserEntity::class);
        $authRepo = $this->_em->getRepository(AuthenticationMappingEntity::class);
        if (!empty($staleVerificationRecords)) {
            foreach ($staleVerificationRecords as $staleVerificationRecord) {
                if ($deleteUserEntities) {
                    $user = $userRepo->find($staleVerificationRecord['uid']);
                    if (null !== $user) {
                        $deletedUsers[] = $user;
                        // delete user
                        $this->_em->remove($user);
                    }
                    $mapping = $authRepo->findOneBy(['uid' => $staleVerificationRecord['uid']]);
                    if (null !== $mapping) {
                        // delete mapping
                        $this->_em->remove($mapping);
                    }
                }

                // delete verification record
                $this->_em->remove($staleVerificationRecord);
            }
            $this->_em->flush();
        }

        return $deletedUsers;
    }

    public function resetVerifyChgFor(int $userId, $types = null): void
    {
        $qb = $this->createQueryBuilder('v')
            ->delete()
            ->where('v.uid = :uid')
            ->setParameter('uid', $userId);
        if (isset($types)) {
            $qb->andWhere($qb->expr()->in('v.changetype', ':changeType'))
                ->setParameter('changeType', $types);
        }
        $query = $qb->getQuery();
        $query->execute();
    }

    public function isVerificationEmailSent(int $userId): bool
    {
        /** @var UserVerificationEntity $userVerification */
        $userVerification = $this->findOneBy(['uid' => $userId]);

        return null !== $userVerification && null !== $userVerification->getCreatedDate();
    }

    public function setVerificationCode(
        int $userId,
        int $changeType = ZAuthConstant::VERIFYCHGTYPE_PWD,
        string $hashedConfirmationCode = null,
        string $email = null
    ): void {
        if (empty($hashedConfirmationCode)) {
            throw new InvalidArgumentException();
        }
        $nowUTC = new DateTime('now', new DateTimeZone('UTC'));

        $query = $this->createQueryBuilder('v')
            ->delete()
            ->where('v.uid = :uid')
            ->andWhere('v.changetype = :changeType')
            ->setParameter('uid', $userId)
            ->setParameter('changeType', $changeType)
            ->getQuery();
        $query->execute();

        $entity = new UserVerificationEntity();
        $entity->setChangetype($changeType);
        $entity->setUid($userId);
        $entity->setVerifycode($hashedConfirmationCode);
        $entity->setCreatedDate($nowUTC);
        if (!empty($email)) {
            $entity->setNewemail($email);
        }
        $this->_em->persist($entity);
        $this->_em->flush();
    }
}
