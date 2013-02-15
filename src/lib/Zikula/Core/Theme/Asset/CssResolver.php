<?php

namespace Zikula\Core\Theme\Asset;

use Zikula\Core\Theme\AssetBag;

class CssResolver
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
}
