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

namespace Zikula\CategoriesModule\Helper;

use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Repository\CategoryRepositoryInterface;

class TreeMapHelper
{
    private int $index;

    private int $level;

    public function __construct(private readonly ManagerRegistry $doctrine, private readonly CategoryRepositoryInterface $repository)
    {
    }

    /**
     * Map tree with parents/children to nested tree.
     */
    public function map(): void
    {
        $this->index = 1;
        $this->level = 0;
        /** @var CategoryEntity $root */
        $root = $this->repository->find(1);
        $root->setLvl($this->level);
        $root->setLft($this->index);
        $this->setTreePropertiesForChildren($root->getChildren());
        $root->setRgt(++$this->index);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Recursive method to properly set lft/rgt/lvl properties.
     */
    private function setTreePropertiesForChildren(Collection $children): void
    {
        $this->level++;
        foreach ($children as $child) {
            $child->setLvl($this->level);
            $child->setLft(++$this->index);
            if ($child->getChildren()->count() > 0) {
                $this->setTreePropertiesForChildren($child->getChildren());
            }
            $child->setRgt(++$this->index);
        }
        $this->level--;
    }
}
