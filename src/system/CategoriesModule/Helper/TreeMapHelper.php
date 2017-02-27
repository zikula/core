<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Helper;

use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;

class TreeMapHelper
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var integer
     */
    private $index;

    /**
     * @var integer
     */
    private $level;

    /**
     * TreeMapHelper constructor.
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->repository = $doctrine->getRepository('ZikulaCategoriesModule:CategoryEntity');
    }

    /**
     * map tree with parents/children to NestedTree
     */
    public function map()
    {
        $this->index = 1;
        $this->level = 0;
        $root = $this->repository->find(1);
        $root->setLvl($this->level);
        $root->setLft($this->index);
        $this->setTreePropertiesForChildren($root->getChildren());
        $root->setRgt(++$this->index);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Recursive method to properly set lft/rgt/lvl properties
     * @param Collection $children
     */
    private function setTreePropertiesForChildren(Collection $children)
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
