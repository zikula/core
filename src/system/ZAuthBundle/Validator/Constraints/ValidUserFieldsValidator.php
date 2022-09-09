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

namespace Zikula\ZAuthBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ZAuthBundle\Entity\AuthenticationMapping;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthBundle\Repository\UserVerificationRepositoryInterface;
use Zikula\ZAuthBundle\ZAuthConstant;

class ValidUserFieldsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly AuthenticationMappingRepositoryInterface $mappingRepository,
        private readonly UserVerificationRepositoryInterface $userVerificationRepository
    ) {
    }

    public function validate($authenticationMappingEntity, Constraint $constraint)
    {
        if (!$constraint instanceof ValidUserFields) {
            throw new UnexpectedTypeException($constraint, ValidUserFields::class);
        }
        $userName = $authenticationMappingEntity->getUname();
        $emailAddress = $authenticationMappingEntity->getEmail();

        // Validate uname and pass are not the same.
        /** @var AuthenticationMapping $authenticationMappingEntity */
        if ($userName === $authenticationMappingEntity->getPass()) {
            $this->context->buildViolation($this->translator->trans('The password cannot be the same as the user name. Please choose a different password.', [], 'validators'))
                ->atPath('pass')
                ->addViolation();
        }
        // Ensure unique uname.
        $qb = $this->mappingRepository->createQueryBuilder('m');
        $qb->select('COUNT(m.uid)')
            ->where($qb->expr()->eq('LOWER(m.uname)', ':uname'))
            ->setParameter('uname', $userName);
        // when updating an existing User, the existing Uid must be excluded.
        if (null !== $authenticationMappingEntity->getUid()) {
            $qb->andWhere('m.uid != :excludedUid')
                ->setParameter('excludedUid', $authenticationMappingEntity->getUid());
        }

        if (0 < (int) $qb->getQuery()->getSingleScalarResult()) {
            $this->context->buildViolation($this->translator->trans('The user name you entered (%userName%) has already been registered.', ['%userName%' => $userName], 'validators'))
                ->atPath('uname')
                ->addViolation();
        }

        // Ensure unique email from both mapping and verification entities if authenticationMethod = native_email or native_either.
        $qb = $this->mappingRepository->createQueryBuilder('m')
            ->select('COUNT(m.uid)')
            ->where('m.email = :email')
            ->andWhere('m.method IN (:methods)')
            ->setParameter('email', $emailAddress)
            ->setParameter('methods', [ZAuthConstant::AUTHENTICATION_METHOD_EITHER, ZAuthConstant::AUTHENTICATION_METHOD_EMAIL])
        ;
        // when updating an existing User, the existing Uid must be excluded.
        if (null !== $authenticationMappingEntity->getUid()) {
            $qb->andWhere('m.uid != :excludedUid')
                ->setParameter('excludedUid', $authenticationMappingEntity->getUid());
        }
        $uCount = (int) $qb->getQuery()->getSingleScalarResult();

        $query = $this->userVerificationRepository->createQueryBuilder('v')
            ->select('COUNT(v.uid)')
            ->where('v.newemail = :email')
            ->andWhere('v.changetype = :chgtype')
            ->setParameter('email', $emailAddress)
            ->setParameter('chgtype', ZAuthConstant::VERIFYCHGTYPE_EMAIL)
            ->getQuery();
        $vCount = (int) $query->getSingleScalarResult();

        if (0 < $uCount + $vCount) {
            $this->context->buildViolation($this->translator->trans('The email address you entered (%email%) has already been registered.', ['%email%' => $emailAddress], 'validators'))
                ->atPath('email')
                ->addViolation();
        }
    }
}