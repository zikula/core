<?php

namespace Zikula\Core\Theme\Asset;

use Zikula\Core\Theme\AssetBag;

/**
 * Class JsResolver
 * @package Zikula\Core\Theme\Asset
 *
 * This class compiles all js page assets into proper html code for inclusion into a page header or footer
 */
class JsResolver implements ResolverInterface
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
            $headers .= '<script type="text/javascript" src="'.$asset.'"></script>'."\n";
        }

        return $headers;
    }

    public function getBag()
    {
        return $this->bag;
    }
}
