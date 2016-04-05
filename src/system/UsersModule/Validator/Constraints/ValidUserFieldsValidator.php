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

    public function validate($object, Constraint $constraint)
    {
        // @todo the $object will eventually be an actual UserEntity object...
        if ($object['uname'] == $object['pass']) {
            $this->context->buildViolation($this->translator->__('The password cannot be the same as the user name. Please choose a different password.'))
                ->atPath('pass') // @todo not currently working - maybe works when $object becomes the entity
                ->addViolation();
        }
        if ($this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED, UsersConstant::DEFAULT_PASSWORD_REMINDER_ENABLED)) {
            $testPass = mb_strtolower(trim($object['pass']));
            $testPassreminder = mb_strtolower(trim($object['passreminder']));
            if (!empty($testPass) && (strlen($testPassreminder) >= strlen($testPass)) && (stristr($testPassreminder, $testPass) !== false)) {
                $this->context->buildViolation($this->translator->__('You cannot include your password in your password reminder.'))
                    ->atPath('passreminder') // @todo not currently working - maybe works when $object becomes the entity
                    ->addViolation();
            }
            // note: removed 'similar' reminder <=> pass comparison from <= Core-1.4.2
        }
    }
}
