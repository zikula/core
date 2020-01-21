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

namespace Zikula\UsersModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;

class ValidEmailValidator extends ConstraintValidator
{
    /**
     * @var VariableApiInterface
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

    public function __construct(
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        ValidatorInterface $validator
    ) {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->validator = $validator;
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($value, [
            new Email()
        ]);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                // this method forces the error to appear at the form input location instead of at the top of the form
                $this->context->buildViolation($error->getMessage())->addViolation();
            }
        }

        // ensure legal domain
        $illegalDomains = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS, '');
        $pattern = ['/^((\s*,)*\s*)+/D', '/\b(\s*,\s*)+\b/D', '/((\s*,)*\s*)+$/D'];
        $replace = ['', '|', ''];
        $illegalDomains = preg_replace($pattern, $replace, preg_quote($illegalDomains, '/'));
        if (!empty($illegalDomains)) {
            $emailDomain = mb_strstr($value, '@');
            if (preg_match("/@({$illegalDomains})/iD", $emailDomain)) {
                $this->context->buildViolation($this->translator->trans('Sorry! The domain of the e-mail address you specified is banned.', [], 'validators'))
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }
    }
}
