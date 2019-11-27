<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Entity\RepositoryInterface;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ThemeModule\Entity\ThemeEntity;

interface ThemeEntityRepositoryInterface
{
    /**
     * @return array|bool
     */
    public function get(
        int $filter,
        int $state,
        int $type
    );

    public function removeAndFlush(ThemeEntity $entity): void;

    public function persistAndFlush(ThemeEntity $entity): void;

    public function setKernel(ZikulaHttpKernelInterface $kernel): void;
}
