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
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

class ValidPasswordChangeValidator extends ConstraintValidator
{
    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $repository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PasswordApiInterface
     */
    private $passwordApi;

    public function __construct(AuthenticationMappingRepositoryInterface $repository, TranslatorInterface $translator, PasswordApiInterface $passwordApi)
    {
        $this->repository = $repository;
        $this->translator = $translator;
        $this->passwordApi = $passwordApi;
    }

    public function validate($data, Constraint $constraint)
    {
        $userEntity = $this->repository->findOneBy(['uid' => $data['uid']]);
        if ($userEntity) {
            $currentPass = $userEntity->getPass();
            // is oldpass correct?
            if (empty($data['oldpass']) || !$this->passwordApi->passwordsMatch($data['oldpass'], $currentPass)) {
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
