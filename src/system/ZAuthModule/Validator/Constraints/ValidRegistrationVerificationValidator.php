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
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
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
     * @var PasswordApiInterface
     */
    private $passwordApi;

    public function __construct(
        TranslatorInterface $translator,
        UserRepositoryInterface $userRepository,
        UserVerificationRepositoryInterface $userVerificationRepository,
        PasswordApiInterface $passwordApi
    ) {
        $this->translator = $translator;
        $this->userRepository = $userRepository;
        $this->userVerificationRepository = $userVerificationRepository;
        $this->passwordApi = $passwordApi;
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
            $codesMatch = $this->passwordApi->passwordsMatch($data['verifycode'], $verifyChg['verifycode']);
            if (!$codesMatch) {
                $this->context->buildViolation($this->translator->trans('The code is invalid for this username.', [], 'validators'))
                    ->atPath('verifycode')
                    ->addViolation();
            }
        }
    }
}
