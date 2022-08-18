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

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\ZAuthBundle\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthBundle\Repository\UserVerificationRepositoryInterface;
use Zikula\ZAuthBundle\ZAuthConstant;

class ValidRegistrationVerificationValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserVerificationRepositoryInterface $userVerificationRepository,
        private readonly EncoderFactoryInterface $encoderFactory
    ) {
    }

    public function validate($data, Constraint $constraint)
    {
        if (!$constraint instanceof ValidRegistrationVerification) {
            throw new UnexpectedTypeException($constraint, ValidRegistrationVerification::class);
        }
        $userEntity = $this->userRepository->findOneBy(['uname' => $data['uname']]);
        if (!$userEntity) {
            $this->context->buildViolation($this->translator->trans('Invalid username.'))
                ->atPath('uname')
                ->addViolation();
        }
        $verifyChg = $this->userVerificationRepository->findOneBy(['uid' => $userEntity->getUid(), 'changetype' => ZAuthConstant::VERIFYCHGTYPE_REGEMAIL]);
        if (!$verifyChg) {
            $this->context->buildViolation($this->translator->trans('Invalid username.', [], 'validators'))
                ->atPath('uname')
                ->addViolation();
        } else {
            $validCode = $this->encoderFactory->getEncoder(AuthenticationMappingEntity::class)->isPasswordValid($verifyChg['verifycode'], $data['verifycode'], null);
            if (!$validCode) {
                $this->context->buildViolation($this->translator->trans('The code is invalid for this username.', [], 'validators'))
                    ->atPath('verifycode')
                    ->addViolation();
            }
        }
    }
}
