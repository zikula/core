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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ZAuthModule\ZAuthConstant;

class ValidPasswordValidator extends ConstraintValidator
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(VariableApiInterface $variableApi, ValidatorInterface $validator)
    {
        $this->variableApi = $variableApi;
        $this->validator = $validator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidPassword) {
            throw new UnexpectedTypeException($constraint, ValidPassword::class);
        }
        if (null === $value || '' === $value) {
            // user created without password
            // needs to set one during verification
            return;
        }
        $validators = [
            new Type('string'),
            new Length([
                'min' => $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::PASSWORD_MINIMUM_LENGTH)
            ])
        ];
        if ($this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_REQUIRE_NON_COMPROMISED_PASSWORD, ZAuthConstant::DEFAULT_REQUIRE_UNCOMPROMISED_PASSWORD)) {
            $validators[] = new NotCompromisedPassword();
        }
        /** @var ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($value, $validators);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                // this method forces the error to appear at the form input location instead of at the top of the form
                $this->context->buildViolation($error->getMessage())->addViolation();
            }
        }
    }
}
