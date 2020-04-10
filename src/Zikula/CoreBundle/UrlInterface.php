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

namespace Zikula\Bundle\CoreBundle;

/**
 * UrlInterface class.
 */
interface UrlInterface
{
    public function getLanguage(): ?string;

    public function getFragment(): ?string;

    public function getArgs(): array;

    public function serialize(): string;

    public function toArray(): array;
}
