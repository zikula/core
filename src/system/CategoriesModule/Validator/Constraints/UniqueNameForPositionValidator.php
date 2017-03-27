<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class UniqueNameForPositionValidator extends ConstraintValidator
{
    use TranslatorTrait;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(TranslatorInterface $translator, CategoryRepositoryInterface $categoryRepository)
    {
        $this->setTranslator($translator);
        $this->categoryRepository = $categoryRepository;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function validate($category, Constraint $constraint)
    {
        $existing = $this->categoryRepository->countForContext($category->getName(), $category->getParent()->getId(), $category->getId());
        if ($existing > 0) {
            $this->context->buildViolation($this->translator->__f('Category %s must be unique under parent', ['%s' => $category->getName()]))
                ->addViolation();
        }
    }
}
