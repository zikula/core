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
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\Repository\UserRepository;

class ValidPasswordChangeValidator extends ConstraintValidator
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ValidPasswordChangeValidator constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    public function validate($data, Constraint $constraint)
    {
        $userEntity = $this->userRepository->find($data['uid']);
        if ($userEntity) {
            $currentPass = $userEntity->getPass();
            // is oldpass correct?
            if ('' != $currentPass && UsersConstant::PWD_NO_USERS_AUTHENTICATION != $currentPass) {
                if (empty($data['oldpass']) || !\UserUtil::passwordsMatch($data['oldpass'], $currentPass)) {
                    $this->context->buildViolation($this->translator->__('Old password is incorrect.'))
                        ->atPath('oldpass')
                        ->addViolation();
                }
            }
            // oldpass == newpass??
            if (isset($data['pass']) && $data['oldpass'] == $data['pass']) {
                $this->context->buildViolation($this->translator->__('Your new password cannot match your current password.'))
                    ->atPath('pass')
                    ->addViolation();
            }
        } else {
            $this->context->buildViolation($this->translator->__('Could not find user to update.'))
                ->atPath('oldpass')
                ->addViolation();
        }
    }
}
