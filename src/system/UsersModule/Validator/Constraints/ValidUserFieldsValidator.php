<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class ValidUserFieldsValidator extends ConstraintValidator
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @param TranslatorInterface $translator
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        UserRepositoryInterface $userRepository
    ) {
        $this->translator = $translator;
        $this->userRepository = $userRepository;
    }

    public function validate($userEntity, Constraint $constraint)
    {
        // ensure unique uname
        $qb = $this->userRepository->createQueryBuilder('u');
        $qb->select('count(u.uid)')
            ->where($qb->expr()->eq('LOWER(u.uname)', ':uname'))
            ->setParameter('uname', $userEntity->getUname());
        // when updating an existing User, the existing Uid must be excluded.
        if (null !== $userEntity->getUid()) {
            $qb->andWhere('u.uid <> :excludedUid')
                ->setParameter('excludedUid', $userEntity->getUid());
        }

        if ((int)$qb->getQuery()->getSingleScalarResult() > 0) {
            $this->context->buildViolation($this->translator->__f('The user name you entered (%u) has already been registered.', ['%u' => $userEntity->getUname()]))
                ->atPath('uname')
                ->addViolation();
        }
    }
}
