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
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersModule\Entity\Repository\UserRepository;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class ValidUserFieldsValidator extends ConstraintValidator
{
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
        // ensure unique uname
        $qb = $this->userRepository->createQueryBuilder('u');
        $qb->select('count(u.uid)')
            ->where($qb->expr()->eq('LOWER(u.uname)', ':uname'))
            ->setParameter('uname', $data['uname']);
        // when updating an existing User, the existing Uid must be excluded.
        if (isset($data['uid']) && is_numeric($data['uid'])) {
            $qb->andWhere('u.uid != :excludedUid')
                ->setParameter('excludedUid', $data['uid']);
        }

        if ((int)$qb->getQuery()->getSingleScalarResult() > 0) {
            $this->context->buildViolation($this->translator->trans('The user name you entered (%userName%) has already been registered.', ['%userName%' => $data['uname']], 'validators'))
                ->atPath('uname')
                ->addViolation();
        }
    }
}
