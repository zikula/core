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

namespace Zikula\ThemeModule\Engine\Asset;

use Zikula\ThemeModule\Engine\AssetBag;

/**
 * Class CssResolver
 *
 * This class compiles all css page assets into proper html code for inclusion into a page header
 */
class CssResolver implements ResolverInterface
{
    private bool $combine;

    public function __construct(
        string $env,
        private readonly AssetBag $bag,
        private readonly MergerInterface $merger,
        bool $combine = false
    ) {
        $this->combine = ('prod' === $env) && $combine;
    }

    public function compile(): string
    {
        $assets = $this->bag->allWithWeight();
        if ($this->combine) {
            $assets = $this->merger->merge($assets, 'css');
        }
        $headers = '';
        foreach ($assets as $asset => $weight) {
            $headers .= '<link rel="stylesheet" href="' . $asset . '" />' . "\n";
        }

        return $headers;
    }

    public function getBag(): AssetBag
    {
        return $this->bag;
    }
}
