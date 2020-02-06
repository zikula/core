<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Helper;

class ExtractTranslationHelper
{
    /**
     * @var string
     */
    private $extractedBundle = '';

    /**
     * Returns the currently extracted bundle.
     */
    public function getBundleName(): string
    {
        return $this->extractedBundle;
    }

    /**
     * Sets the currently extracted bundle.
     */
    public function setExtensionName(string $bundle): void
    {
        $this->extractedBundle = $bundle;
    }
}
