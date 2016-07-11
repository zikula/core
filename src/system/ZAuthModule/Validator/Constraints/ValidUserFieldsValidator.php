<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\ZAuthConstant;

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
     * @var UserRepositoryInterface
     */
    private $mappingRepository;

    /**
     * @var UserVerificationRepositoryInterface
     */
    private $userVerificationRepository;

    /**
     * @param VariableApi $variableApi
     * @param TranslatorInterface $translator
     * @param AuthenticationMappingRepositoryInterface $mappingRepository
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     */
    public function __construct(
        VariableApi $variableApi,
        TranslatorInterface $translator,
        AuthenticationMappingRepositoryInterface $mappingRepository,
        UserVerificationRepositoryInterface $userVerificationRepository
    ) {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->mappingRepository = $mappingRepository;
        $this->userVerificationRepository = $userVerificationRepository;
    }

    public function validate($authenticationMappingEntity, Constraint $constraint)
    {
        // Validate uname and pass are not the same.
        /** @var AuthenticationMappingEntity $authenticationMappingEntity */
        if ($authenticationMappingEntity->getUname() == $authenticationMappingEntity->getPass()) {
            $this->context->buildViolation($this->translator->__('The password cannot be the same as the user name. Please choose a different password.'))
                ->atPath('pass')
                ->addViolation();
        }
        // Validate password not included in the password reminder.
        if ($this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_REMINDER_ENABLED, ZAuthConstant::DEFAULT_PASSWORD_REMINDER_ENABLED)) {
            $testPass = mb_strtolower(trim($authenticationMappingEntity->getPass()));
            $testPassreminder = mb_strtolower(trim($authenticationMappingEntity->getPassreminder()));
            if (!empty($testPass) && (strlen($testPassreminder) >= strlen($testPass)) && (stristr($testPassreminder, $testPass) !== false)) {
                $this->context->buildViolation($this->translator->__('You cannot include your password in your password reminder.'))
                    ->atPath('passreminder')
                    ->addViolation();
            }
            // note: removed 'too similar' reminder <=> pass comparison from <= Core-1.4.2
        }
        // Ensure unique uname.
        $qb = $this->mappingRepository->createQueryBuilder('m');
        $qb->select('count(m.uid)')
            ->where($qb->expr()->eq('LOWER(m.uname)', ':uname'))
            ->setParameter('uname', $authenticationMappingEntity->getUname());
        // when updating an existing User, the existing Uid must be excluded.
        if (null !== $authenticationMappingEntity->getUid()) {
            $qb->andWhere('m.uid <> :excludedUid')
                ->setParameter('excludedUid', $authenticationMappingEntity->getUid());
        }

        if ((int)$qb->getQuery()->getSingleScalarResult() > 0) {
            $this->context->buildViolation($this->translator->__f('The user name you entered (%u) has already been registered.', ['%u' => $authenticationMappingEntity->getUname()]))
                ->atPath('uname')
                ->addViolation();
        }

        // Ensure unique email from both mapping and verification entities if authenticationMethod = native_email or native_either.
        $qb = $this->mappingRepository->createQueryBuilder('m')
            ->select('count(m.uid)')
            ->where('m.email = :email')
            ->andWhere('m.method IN (:methods)')
            ->setParameter('email', $authenticationMappingEntity->getEmail())
            ->setParameter('methods', [ZAuthConstant::AUTHENTICATION_METHOD_EITHER, 'native_email'])
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
            ->setParameter('email', $authenticationMappingEntity->getEmail())
            ->setParameter('chgtype', ZAuthConstant::VERIFYCHGTYPE_EMAIL)
            ->getQuery();
        $vCount = (int)$query->getSingleScalarResult();

        if ($uCount + $vCount > 0) {
            $this->context->buildViolation($this->translator->__('The email address you entered has already been registered.'))
                ->atPath('email')
                ->addViolation();
        }
    }
}
