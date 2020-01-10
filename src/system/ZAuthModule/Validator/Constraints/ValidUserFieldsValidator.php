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

namespace Zikula\ZAuthModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\Repository\AuthenticationMappingRepository;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\ZAuthConstant;

class ValidUserFieldsValidator extends ConstraintValidator
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $mappingRepository;

    /**
     * @var UserVerificationRepositoryInterface
     */
    private $userVerificationRepository;

    public function __construct(
        TranslatorInterface $translator,
        AuthenticationMappingRepository $mappingRepository,
        UserVerificationRepositoryInterface $userVerificationRepository
    ) {
        $this->translator = $translator;
        $this->mappingRepository = $mappingRepository;
        $this->userVerificationRepository = $userVerificationRepository;
    }

    public function validate($authenticationMappingEntity, Constraint $constraint)
    {
        $userName = $authenticationMappingEntity->getUname();
        $emailAddress = $authenticationMappingEntity->getEmail();

        // Validate uname and pass are not the same.
        /** @var AuthenticationMappingEntity $authenticationMappingEntity */
        if ($userName === $authenticationMappingEntity->getPass()) {
            $this->context->buildViolation($this->translator->trans('The password cannot be the same as the user name. Please choose a different password.'))
                ->atPath('pass')
                ->addViolation();
        }
        // Ensure unique uname.
        $qb = $this->mappingRepository->createQueryBuilder('m');
        $qb->select('count(m.uid)')
            ->where($qb->expr()->eq('LOWER(m.uname)', ':uname'))
            ->setParameter('uname', $userName);
        // when updating an existing User, the existing Uid must be excluded.
        if (null !== $authenticationMappingEntity->getUid()) {
            $qb->andWhere('m.uid <> :excludedUid')
                ->setParameter('excludedUid', $authenticationMappingEntity->getUid());
        }

        if ((int)$qb->getQuery()->getSingleScalarResult() > 0) {
            $this->context->buildViolation($this->translator->trans('The user name you entered (%userName%) has already been registered.', ['%userName%' => $userName]))
                ->atPath('uname')
                ->addViolation();
        }

        // Ensure unique email from both mapping and verification entities if authenticationMethod = native_email or native_either.
        $qb = $this->mappingRepository->createQueryBuilder('m')
            ->select('count(m.uid)')
            ->where('m.email = :email')
            ->andWhere('m.method IN (:methods)')
            ->setParameter('email', $emailAddress)
            ->setParameter('methods', [ZAuthConstant::AUTHENTICATION_METHOD_EITHER, ZAuthConstant::AUTHENTICATION_METHOD_EMAIL])
        ;
        // when updating an existing User, the existing Uid must be excluded.
        if (null !== $authenticationMappingEntity->getUid()) {
            $qb->andWhere('m.uid <> :excludedUid')
                ->setParameter('excludedUid', $authenticationMappingEntity->getUid());
        }
        $uCount = (int)$qb->getQuery()->getSingleScalarResult();

        $query = $this->userVerificationRepository->createQueryBuilder('v')
            ->select('count(v.uid)')
            ->where('v.newemail = :email')
            ->andWhere('v.changetype = :chgtype')
            ->setParameter('email', $emailAddress)
            ->setParameter('chgtype', ZAuthConstant::VERIFYCHGTYPE_EMAIL)
            ->getQuery();
        $vCount = (int)$query->getSingleScalarResult();

        if ($uCount + $vCount > 0) {
            $this->context->buildViolation($this->translator->trans('The email address you entered (%email%) has already been registered.', ['%email%' => $emailAddress]))
                ->atPath('email')
                ->addViolation();
        }
    }
}
