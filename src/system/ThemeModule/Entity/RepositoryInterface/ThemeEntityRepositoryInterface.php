<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
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
        int $state
    );

    public function removeAndFlush(ThemeEntity $entity): void;

    public function persistAndFlush(ThemeEntity $entity): void;

    public function setKernel(ZikulaHttpKernelInterface $kernel): void;
}
