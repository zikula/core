<?php

/*
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
 * Class JsResolver
 *
 * This class compiles all js page assets into proper html code for inclusion into a page header or footer
 */
class JsResolver implements ResolverInterface
{
    /**
     * @var AssetBag
     */
    private $bag;

    /**
     * @var MergerInterface
     */
    private $merger;

    /**
     * @var bool
     */
    private $combine;

    /**
     * JsResolver constructor.
     * @param AssetBag $bag
     * @param MergerInterface $merger
     * @param string $env
     * @param bool $combine
     */
    public function __construct(AssetBag $bag, MergerInterface $merger, $env = 'prod', $combine = false)
    {
        $this->bag = $bag;
        $this->merger = $merger;
        $this->combine = $env == 'prod' && $combine;
    }

    public function compile()
    {
        $assets = $this->bag->all();
        if ($this->combine) {
            $assets = $this->merger->merge($assets, 'js');
        }
        $headers = '';
        foreach ($assets as $asset) {
            $headers .= '<script type="text/javascript" src="' . $asset . '"></script>' . "\n";
        }

        return $headers;
    }

    public function getBag()
    {
        return $this->bag;
    }
}
