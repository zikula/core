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

namespace Zikula\UsersModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\Repository\UserRepository;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class ValidUserFieldsValidator extends ConstraintValidator
{
    public const DUP_EMAIL_ALT_AUTH = 'DuplicateEmailOfAlternativeAuthMethod';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        TranslatorInterface $translator,
        UserRepositoryInterface $userRepository
    ) {
        $this->translator = $translator;
        $this->userRepository = $userRepository;
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
        if ($this->userRepository->countDuplicateUnames($uname, $uid) > 0) {
            $this->context->buildViolation($this->translator->trans('The user name you entered (%userName%) has already been registered.', ['%userName%' => $uname], 'validators'))
                ->atPath('uname')
                ->addViolation();
        }
    }

    private function validateEmailWithAuth(UserEntity $data): void
    {
        $authMethod = $data->getAttributeValue(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY);
        $existing = $this->userRepository->getByEmailAndAuthMethod($data['email'], $authMethod);
        if (count($existing) > 0) {
            $this->context->buildViolation($this->translator->trans('This email is in use by another authentication method. Please login with that method instead.', [], 'validators'))
                ->atPath('email')
                ->setCode(self::DUP_EMAIL_ALT_AUTH)
                ->addViolation();
        }
    }
}
