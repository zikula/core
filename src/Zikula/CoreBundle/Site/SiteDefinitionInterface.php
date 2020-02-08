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

namespace Zikula\Bundle\CoreBundle\Site;

interface SiteDefinitionInterface
{
    public function getName(): string;

    public function getSlogan(): string;

    public function getPageTitle(): string;

    public function getMetaDescription(): string;

    public function getLogoPath(): ?string;

    public function getMobileLogoPath(): ?string;

    public function getIconPath(): ?string;
}
