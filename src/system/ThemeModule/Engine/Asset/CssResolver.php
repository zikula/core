<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine\Asset;

use Zikula\ThemeModule\Engine\AssetBag;

/**
 * Class CssResolver
 * @package Zikula\ThemeModule\Engine\Asset
 *
 * This class compiles all css page assets into proper html code for inclusion into a page header
 */
class CssResolver implements ResolverInterface
{
    private $bag;

    public function __construct(AssetBag $bag)
    {
        $this->bag = $bag;
    }

    public function compile()
    {
        $headers = '';
        foreach ($this->bag->all() as $asset) {
            $headers .= '<link rel="stylesheet" href="'.$asset.'" type="text/css">'."\n";
        }

        return $headers;
    }

    public function getBag()
    {
        return $this->bag;
    }
}
