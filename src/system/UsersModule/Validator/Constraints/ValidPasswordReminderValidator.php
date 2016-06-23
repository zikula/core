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
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\ZAuthModule\ZAuthConstant;

class ValidPasswordReminderValidator extends ConstraintValidator
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param VariableApi $variableApi
     * @param ValidatorInterface $validator
     */
    public function __construct(VariableApi $variableApi, ValidatorInterface $validator)
    {
        $this->variableApi = $variableApi;
        $this->validator = $validator;
    }

    public function validate($value, Constraint $constraint)
    {
        if ($this->variableApi->get('ZikulaUsersModule', ZAuthConstant::MODVAR_PASSWORD_REMINDER_ENABLED, ZAuthConstant::DEFAULT_PASSWORD_REMINDER_ENABLED)) {
            /** @var ConstraintViolationListInterface $errors */
            $errors = $this->validator->validate($value, [
                new NotNull(),
                new Type('string'),
            ]);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    // this method forces the error to appear at the form input location instead of at the top of the form
                    $this->context->buildViolation($error->getMessage())->addViolation();
                }
            }
        }
    }
}
