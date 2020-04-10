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

namespace Zikula\MenuModule\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\MenuModule\Entity\RepositoryInterface\MenuItemRepositoryInterface;

class MenuItemEntityTransformer implements DataTransformerInterface
{
    /**
     * @var MenuItemRepositoryInterface
     */
    private $repo;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(MenuItemRepositoryInterface $repo, TranslatorInterface $translator)
    {
        $this->repo = $repo;
        $this->translator = $translator;
    }

    public function reverseTransform($value)
    {
        if (empty($value)) {
            return null;
        }

        $entity = $this->repo->find($value);
        if (null === $entity) {
            throw new TransformationFailedException($this->translator->trans('That entity does not exist!'));
        }

        return $entity;
    }

    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        return $value->getId();
    }
}
