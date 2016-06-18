<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\Repository\UserRepository;
use Zikula\UsersModule\Entity\Repository\UserVerificationRepository;
use Zikula\UsersModule\Entity\UserEntity;

class ValidUserFieldsValidator extends ConstraintValidator
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UserRepository
     * @todo refactor to UserRepositoryInterface when appropriate
     */
    private $userRepository;

    /**
     * @var UserVerificationRepository
     */
    private $userVerificationRepository;

    /**
     * @param VariableApi $variableApi
     * @param TranslatorInterface $translator
     * @param UserRepository $userRepository
     * @param UserVerificationRepository $userVerificationRepository
     */
    public function __construct(VariableApi $variableApi, TranslatorInterface $translator, UserRepository $userRepository, UserVerificationRepository $userVerificationRepository)
    {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
        $this->userVerificationRepository = $userVerificationRepository;
    }

    public function validate($userEntity, Constraint $constraint)
    {
        /** @var UserEntity $userEntity */
        if ($userEntity->getUname() == $userEntity->getPass()) {
            $this->context->buildViolation($this->translator->__('The password cannot be the same as the user name. Please choose a different password.'))
                ->atPath('pass')
                ->addViolation();
        }
        if ($this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED, UsersConstant::DEFAULT_PASSWORD_REMINDER_ENABLED)) {
            $testPass = mb_strtolower(trim($userEntity->getPass()));
            $testPassreminder = mb_strtolower(trim($userEntity->getPassreminder()));
            if (!empty($testPass) && (strlen($testPassreminder) >= strlen($testPass)) && (stristr($testPassreminder, $testPass) !== false)) {
                $this->context->buildViolation($this->translator->__('You cannot include your password in your password reminder.'))
                    ->atPath('passreminder')
                    ->addViolation();
            }
            // note: removed 'similar' reminder <=> pass comparison from <= Core-1.4.2
        }
        // ensure unique uname
        $qb = $this->userRepository->createQueryBuilder('u');
        $qb->select('count(u.uid)')
            ->where($qb->expr()->eq('LOWER(u.uname)', ':uname'))
            ->setParameter('uname', $userEntity->getUname());
        // when updating an existing User, the existing Uid must be excluded.
        if (null !== $userEntity->getUid()) {
            $qb->andWhere('u.uid <> :excludedUid')
                ->setParameter('excludedUid', $userEntity->getUid());
        }

        if ((int)$qb->getQuery()->getSingleScalarResult() > 0) {
            $this->context->buildViolation($this->translator->__('The user name you entered has already been registered.'))
                ->atPath('uname')
                ->addViolation();
        }

        // ensure unique email from both user and verification entities
        if ($this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REQUIRE_UNIQUE_EMAIL, false)) {
            $qb = $this->userRepository->createQueryBuilder('u')
                ->select('count(u.uid)')
                ->where('u.email = :email')
                ->setParameter('email', $userEntity->getEmail());
            // when updating an existing User, the existing Uid must be excluded.
            if (null !== $userEntity->getUid()) {
                $qb->andWhere('u.uid <> :excludedUid')
                    ->setParameter('excludedUid', $userEntity->getUid());
            }
            $uCount = (int)$qb->getQuery()->getSingleScalarResult();

            $query = $this->userVerificationRepository->createQueryBuilder('v')
                ->select('count(v.uid)')
                ->where('v.newemail = :email')
                ->andWhere('v.changetype = :chgtype')
                ->setParameter('email', $userEntity->getEmail())
                ->setParameter('chgtype', UsersConstant::VERIFYCHGTYPE_EMAIL)
                ->getQuery();
            $vCount = (int)$query->getSingleScalarResult();

            if ($uCount + $vCount > 0) {
                $this->context->buildViolation($this->translator->__('The email address you entered has already been registered.'))
                    ->atPath('email')
                    ->addViolation();
            }
        }
    }
}
