<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Constant as UsersConstant;

class ValidUnameValidator extends ConstraintValidator
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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param VariableApi $variableApi
     * @param TranslatorInterface $translator
     * @param ValidatorInterface $validator
     */
    public function __construct(VariableApi $variableApi, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->validator = $validator;
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($value, [
            new NotBlank(),
            new Type('string'),
            new Length([
                'min' => 1,
                'max' => UsersConstant::UNAME_VALIDATION_MAX_LENGTH
            ]),
            new Regex([
                'pattern' => '/^' . UsersConstant::UNAME_VALIDATION_PATTERN . '$/uD',
                'message' => $this->translator->__('The value does not appear to be a valid user name. A valid user name consists of lowercase letters, numbers, underscores, periods or dashes.')
            ])
        ]);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                // this method forces the error to appear at the form input location instead of at the top of the form
                $this->context->buildViolation($error->getMessage())->addViolation();
            }
        }

        // ensure not reserved/illegal
        $illegalUserNames = $this->variableApi->get('ZikulaZAuthModule', UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES, '');
        if (!empty($illegalUserNames)) {
            $pattern = ['/^(\s*,\s*|\s+)+/D', '/\b(\s*,\s*|\s+)+\b/D', '/(\s*,\s*|\s+)+$/D'];
            $replace = ['', '|', ''];
            $illegalUserNames = preg_replace($pattern, $replace, preg_quote($illegalUserNames, '/'));
            if (preg_match("/^({$illegalUserNames})/iD", $value)) {
                $this->context->buildViolation($this->translator->__('The user name you entered is reserved. It cannot be used.'))
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }
    }
}
