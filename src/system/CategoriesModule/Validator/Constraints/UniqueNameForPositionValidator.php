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

namespace Zikula\CategoriesModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;

class UniqueNameForPositionValidator extends ConstraintValidator
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(TranslatorInterface $translator, CategoryRepositoryInterface $categoryRepository)
    {
        $this->translator = $translator;
        $this->categoryRepository = $categoryRepository;
    }

    public function validate($category, Constraint $constraint)
    {
        $existing = $this->categoryRepository->countForContext($category->getName(), $category->getParent()->getId(), $category->getId());
        if ($existing > 0) {
            $this->context->buildViolation($this->translator->trans('Category "%name%" must be unique under parent', ['%name%' => $category->getName()], 'validators'))
                ->addViolation()
            ;
        }
    }
}
