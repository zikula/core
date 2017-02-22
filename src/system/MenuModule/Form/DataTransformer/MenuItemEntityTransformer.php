<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Zikula\Common\Translator\TranslatorInterface;
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

    /**
     * MenuItemEntityTransformer constructor.
     * @param MenuItemRepositoryInterface $repo
     * @param TranslatorInterface $translator
     */
    public function __construct(MenuItemRepositoryInterface $repo, TranslatorInterface $translator)
    {
        $this->repo = $repo;
        $this->translator = $translator;
    }

    /**
* @inheritDoc
     */
    public function reverseTransform($value)
    {
        if (null == $value) {
            return null;
        }

        $entity = $this->repo->find($value);
        if (null === $entity) {
            throw new TransformationFailedException($this->translator->__('That entity does not exist!'));
        }

        return $entity;
    }

    /**
* @inheritDoc
     */
    public function transform($value)
    {
        if (null == $value) {
            return null;
        }

        return $value->getId();
    }
}
