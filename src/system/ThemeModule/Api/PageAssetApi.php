<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Api;

use Zikula\ThemeModule\Api\ApiInterface\PageAssetApiInterface;
use Zikula\ThemeModule\Engine\AssetBag;

class PageAssetApi implements PageAssetApiInterface
{
    /**
     * @var AssetBag
     */
    private $styleSheets;

    /**
     * @var AssetBag
     */
    private $scripts;

    /**
     * @var AssetBag
     */
    private $headers;

    /**
     * @var AssetBag
     */
    private $footers;

    /**
     * constructor.
     * @param AssetBag $styleSheets
     * @param AssetBag $scripts
     * @param AssetBag $headers
     * @param AssetBag $footers
     */
    public function __construct(
        AssetBag $styleSheets,
        AssetBag $scripts,
        AssetBag $headers,
        AssetBag $footers
    ) {
        $this->styleSheets = $styleSheets;
        $this->scripts = $scripts;
        $this->headers = $headers;
        $this->footers = $footers;
    }

    /**
     * {@inheritdoc}
     */
    public function add($type, $value, $weight = AssetBag::WEIGHT_DEFAULT)
    {
        if (empty($type) || empty($value)) {
            throw new \InvalidArgumentException();
        }
        if (!in_array($type, ['stylesheet', 'javascript', 'header', 'footer']) || !is_numeric($weight)) {
            throw new \InvalidArgumentException();
        }

        // ensure proper variable types
        $value = (string) $value;
        $type = (string) $type;
        $weight = (int) $weight;

        if ('stylesheet' == $type) {
            $this->styleSheets->add([$value => $weight]);
        } elseif ('javascript' == $type) {
            $this->scripts->add([$value => $weight]);
        } elseif ('header' == $type) {
            $this->headers->add([$value => $weight]);
        } elseif ('footer' == $type) {
            $this->footers->add([$value => $weight]);
        }
    }
}
