<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Twig\Extension;

use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ThemeModule\Engine\Engine;

class AssetExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

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
     * @var Engine
     */
    private $engine;

    /**
     * AssetExtension constructor.
     * @param AssetBag $styleSheets
     * @param AssetBag $scripts
     * @param AssetBag $headers
     * @param AssetBag $footers
     * @param Engine $engine
     */
    public function __construct(
        AssetBag $styleSheets,
        AssetBag $scripts,
        AssetBag $headers,
        AssetBag $footers,
        Engine $engine
    ) {
        $this->styleSheets = $styleSheets;
        $this->scripts = $scripts;
        $this->headers = $headers;
        $this->footers = $footers;
        $this->engine = $engine;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('pageAddAsset', [$this, 'pageAddAsset']),
        ];
    }

    /**
     * Zikula allows only the following asset types
     * <ul>
     *  <li>stylesheet</li>
     *  <li>javascript</li>
     *  <li>header</li>
     *  <li>footer</li>
     * </ul>
     *
     * @param string $type
     * @param string $value
     * @param int $weight
     */
    public function pageAddAsset($type, $value, $weight = AssetBag::WEIGHT_DEFAULT)
    {
        if (empty($type) || empty($value)) {
            throw new \InvalidArgumentException($this->translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }
        if (!in_array($type, ['stylesheet', 'javascript', 'header', 'footer']) || !is_numeric($weight)) {
            throw new \InvalidArgumentException($this->translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
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
