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

namespace Zikula\ThemeModule\Api;

use InvalidArgumentException;
use Zikula\ThemeModule\Api\ApiInterface\PageAssetApiInterface;
use Zikula\ThemeModule\Engine\AssetBag;

class PageAssetApi implements PageAssetApiInterface
{
    public function __construct(
        private readonly AssetBag $styleSheets,
        private readonly AssetBag $scripts,
        private readonly AssetBag $headers,
        private readonly AssetBag $footers
    ) {
    }

    public function add(string $type, string $value, int $weight = AssetBag::WEIGHT_DEFAULT): void
    {
        if (empty($type) || empty($value)) {
            throw new InvalidArgumentException();
        }
        if (!in_array($type, ['stylesheet', 'javascript', 'header', 'footer'])) {
            throw new InvalidArgumentException();
        }

        if ('stylesheet' === $type) {
            $this->styleSheets->add([$value => $weight]);
        } elseif ('javascript' === $type) {
            $this->scripts->add([$value => $weight]);
        } elseif ('header' === $type) {
            $this->headers->add([$value => $weight]);
        } elseif ('footer' === $type) {
            $this->footers->add([$value => $weight]);
        }
    }
}
