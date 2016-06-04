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

class ValidRegistrationVerificationValidator extends ConstraintValidator
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

    public function validate($data, Constraint $constraint)
    {
        $userEntity = $this->userRepository->findOneBy(['uname' => $data['uname']]);
        if (!$userEntity) {
            $this->context->buildViolation($this->translator->__('Invalid username.'))
                ->atPath('uname')
                ->addViolation();
        }
        $verifyChg = $this->userVerificationRepository->findOneBy(['uid' => $userEntity->getUid(), 'changetype' => UsersConstant::VERIFYCHGTYPE_REGEMAIL]);
        if (!$verifyChg) {
            $this->context->buildViolation($this->translator->__('Invalid username.'))
                ->atPath('uname')
                ->addViolation();
        }
        $codesMatch = \UserUtil::passwordsMatch($data['verifycode'], $verifyChg['verifycode']);
        if (!$codesMatch) {
            $this->context->buildViolation($this->translator->__('The code is invalid for this username.'))
                ->atPath('verifycode')
                ->addViolation();
        }
    }
}
