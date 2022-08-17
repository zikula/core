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

namespace Zikula\GroupsModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;

class ValidGroupNameValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly GroupRepositoryInterface $groupRepository
    ) {
    }

    public function validate($data, Constraint $constraint)
    {
        // ensure unique name
        $qb = $this->groupRepository->createQueryBuilder('g');
        $qb->select('count(g.gid)')
            ->where($qb->expr()->eq('LOWER(g.name)', ':name'))
            ->setParameter('name', $data->getName());

        $gid = $data->getGid();
        if (isset($gid)) {
            // when updating an existing group, the existing gid must be excluded.
            $qb->andWhere('g.gid != :excludedGid')
                ->setParameter('excludedGid', $gid);
        }

        if (0 < (int) $qb->getQuery()->getSingleScalarResult()) {
            $this->context->buildViolation(
                $this->translator->trans(
                    'The group name you entered (%groupName%) does already exist.',
                    ['%groupName%' => $data->getName()],
                    'validators'
                )
            )
                ->atPath('name')
                ->addViolation()
            ;
        }
    }
}
