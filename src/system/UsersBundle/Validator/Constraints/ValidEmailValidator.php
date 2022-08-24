<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ValidEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ValidatorInterface $validator,
        private readonly ?string $illegalDomains
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }
        if (!$constraint instanceof ValidEmail) {
            throw new UnexpectedTypeException($constraint, ValidEmail::class);
        }
        /** @var ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($value, [
            new Email(),
        ]);
        if (0 < count($errors)) {
            foreach ($errors as $error) {
                // this method forces the error to appear at the form input location instead of at the top of the form
                $this->context->buildViolation($error->getMessage())->addViolation();
            }
        }

        // ensure legal domain
        $illegalDomains = $this->illegalDomains ?? '';
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
