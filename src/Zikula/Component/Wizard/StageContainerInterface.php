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

namespace Zikula\Component\Wizard;

interface StageContainerInterface
{
    /**
     * @param StageInterface[] $stages
     */
    public function __construct(iterable $stages = []);

    public function add(StageInterface $stage): void;

    public function get(string $id): ?StageInterface;

    public function has(string $id): bool;

    public function all(): array;
}
