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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\UsersConstant;

class ValidUnameValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ValidatorInterface $validator,
        private readonly PermissionApiInterface $permissionApi,
        private readonly ?string $illegalUserNames
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }
        if (!$constraint instanceof ValidUname) {
            throw new UnexpectedTypeException($constraint, ValidUname::class);
        }
        /** @var ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($value, [
            new NotBlank(),
            new Type('string'),
            new Length([
                'min' => 1,
                'max' => UsersConstant::UNAME_VALIDATION_MAX_LENGTH,
            ]),
            new Regex([
                'pattern' => '/^' . UsersConstant::UNAME_VALIDATION_PATTERN . '$/uD',
                /** @Ignore */
                'message' => $this->translator->trans('The value does not appear to be a valid user name. A valid user name consists of lowercase letters, numbers, underscores, periods or dashes.', [], 'validators'),
            ]),
        ]);
        if (0 < count($errors)) {
            foreach ($errors as $error) {
                // this method forces the error to appear at the form input location instead of at the top of the form
                $this->context->buildViolation($error->getMessage())->addViolation();
            }
        }

        // ensure not reserved/illegal (unless performed by Admin)
        $illegalUserNames = $this->illegalUserNames ?? '';
        if (!empty($illegalUserNames) && !$this->permissionApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_ADMIN)) {
            $pattern = ['/^(\s*,\s*|\s+)+/D', '/\b(\s*,\s*|\s+)+\b/D', '/(\s*,\s*|\s+)+$/D'];
            $replace = ['', '|', ''];
            $illegalUserNames = preg_replace($pattern, $replace, preg_quote($illegalUserNames, '/'));
            if (preg_match("/^({$illegalUserNames})/iD", $value)) {
                $this->context->buildViolation($this->translator->trans('The user name you entered is reserved. It cannot be used.', [], 'validators'))
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }
    }
}
