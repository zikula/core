<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;

class ValidGroupNameValidator extends ConstraintValidator
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param TranslatorInterface $translator
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->translator = $translator;
        $this->groupRepository = $groupRepository;
    }

    public function validate($data, Constraint $constraint)
    {
        // ensure unique name
        $qb = $this->groupRepository->createQueryBuilder('g');
        $qb->select('count(g.gid)')
            ->where($qb->expr()->eq('LOWER(g.name)', ':name'))
            ->setParameter('name', $data->getName());
        // when updating an existing Group, the existing gid must be excluded.
        $gid = $data->getGid();
        if (isset($gid)) {
            $qb->andWhere('g.gid <> :excludedGid')
                ->setParameter('excludedGid', $gid);
        }

        if ((int)$qb->getQuery()->getSingleScalarResult() > 0) {
            $this->context->buildViolation($this->translator->__f('The group name you entered (%u) has already been registered.', ['%u' => $data->getName()]))
                ->atPath('name')
                ->addViolation();
        }
    }
}
