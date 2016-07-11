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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Constant as UsersConstant;

class ValidEmailValidator extends ConstraintValidator
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
            new Email()
        ]);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                // this method forces the error to appear at the form input location instead of at the top of the form
                $this->context->buildViolation($error->getMessage())->addViolation();
            }
        }

        // ensure legal domain
        $illegalDomains = $this->variableApi->get('ZikulaZAuthModule', UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS, '');
        $pattern = array('/^((\s*,)*\s*)+/D', '/\b(\s*,\s*)+\b/D', '/((\s*,)*\s*)+$/D');
        $replace = array('', '|', '');
        $illegalDomains = preg_replace($pattern, $replace, preg_quote($illegalDomains, '/'));
        if (!empty($illegalDomains)) {
            $emailDomain = strstr($value, '@');
            if (preg_match("/@({$illegalDomains})/iD", $emailDomain)) {
                $this->context->buildViolation($this->translator->__('Sorry! The domain of the e-mail address you specified is banned.'))
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }
    }
}
