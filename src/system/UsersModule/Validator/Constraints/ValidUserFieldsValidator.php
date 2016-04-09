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
     * @param VariableApi $variableApi
     * @param TranslatorInterface $translator
     */
    public function __construct(VariableApi $variableApi, TranslatorInterface $translator)
    {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
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
    }
}
