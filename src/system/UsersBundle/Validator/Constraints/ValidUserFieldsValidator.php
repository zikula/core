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
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;

class ValidUserFieldsValidator extends ConstraintValidator
{
    public const DUP_EMAIL_ALT_AUTH = 'DuplicateEmailOfAlternativeAuthMethod';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function validate($data, Constraint $constraint)
    {
        if (!$constraint instanceof ValidUserFields) {
            throw new UnexpectedTypeException($constraint, ValidUserFields::class);
        }
        // ensure unique uname
        $this->validateUniqueUname($data['uname'], $data['uid'] ?? null);

        // users registering duplicate email with different authentication method are invalid
        if ($data instanceof UserEntity && $data->hasAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY)) {
            $this->validateEmailWithAuth($data);
        }
    }

    private function validateUniqueUname(string $uname, ?int $uid = null): void
    {
        if (0 < $this->userRepository->countDuplicateUnames($uname, $uid)) {
            $this->context->buildViolation($this->translator->trans('The user name you entered (%userName%) has already been registered.', ['%userName%' => $uname], 'validators'))
                ->atPath('uname')
                ->addViolation();
        }
    }

    private function validateEmailWithAuth(UserEntity $data): void
    {
        $authMethod = $data->getAttributeValue(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY);
        $existing = $this->userRepository->getByEmailAndAuthMethod($data['email'], $authMethod);
        if (0 < count($existing)) {
            $this->context->buildViolation($this->translator->trans('This email is in use by another authentication method. Please login with that method instead.', [], 'validators'))
                ->atPath('email')
                ->setCode(self::DUP_EMAIL_ALT_AUTH)
                ->addViolation();
        }
    }
}
