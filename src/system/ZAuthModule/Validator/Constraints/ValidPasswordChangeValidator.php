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

namespace Zikula\ZAuthModule\Validator\Constraints;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

class ValidPasswordChangeValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AuthenticationMappingRepositoryInterface $repository,
        private readonly TranslatorInterface $translator,
        private readonly EncoderFactoryInterface $encoderFactory
    ) {
    }

    public function validate($data, Constraint $constraint)
    {
        if (!$constraint instanceof ValidPasswordChange) {
            throw new UnexpectedTypeException($constraint, ValidPasswordChange::class);
        }
        $userEntity = $this->repository->findOneBy(['uid' => $data['uid']]);
        if ($userEntity) {
            $currentPass = $userEntity->getPass();
            $validPassword = $this->encoderFactory->getEncoder($userEntity)->isPasswordValid($currentPass, $data['oldpass'], null);
            if (empty($data['oldpass']) || !$validPassword) {
                $this->context->buildViolation($this->translator->trans('Old password is incorrect.', [], 'validators'))
                    ->atPath('oldpass')
                    ->addViolation();
            }
            // oldpass == newpass?
            if (isset($data['pass']) && $data['oldpass'] === $data['pass']) {
                $this->context->buildViolation($this->translator->trans('Your new password cannot match your current password.', [], 'validators'))
                    ->atPath('pass')
                    ->addViolation();
            }
        } else {
            $this->context->buildViolation($this->translator->trans('Could not find user to update.', [], 'validators'))
                ->atPath('oldpass')
                ->addViolation();
        }
    }
}
