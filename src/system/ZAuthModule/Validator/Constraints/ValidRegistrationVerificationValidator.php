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

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\ZAuthConstant;

class ValidRegistrationVerificationValidator extends ConstraintValidator
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserVerificationRepositoryInterface
     */
    private $userVerificationRepository;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    public function __construct(
        TranslatorInterface $translator,
        UserRepositoryInterface $userRepository,
        UserVerificationRepositoryInterface $userVerificationRepository,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->translator = $translator;
        $this->userRepository = $userRepository;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->encoderFactory = $encoderFactory;
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
