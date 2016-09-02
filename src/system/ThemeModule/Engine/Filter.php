<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

use Zikula\ThemeModule\Engine\Asset\ResolverInterface;

/**
 * Class Filter
 *
 * This class resolves, compiles and renders all page assets and adds them to the outgoing source content
 * Recently, the accepted practice for placement of javascript has changed from header to footer. Placement in this
 * class is determined by the `scriptPosition` parameter in `app/config/parameters.yml`. In Core-1.4 this defaults
 * to the header. In Core-2.0 it will default to footer. Scripts must be written to accommodate this change.
 */
class Filter
{
    /**
     * @var AssetBag
     */
    private $headers;

    /**
     * @var AssetBag
     */
    private $footers;

    /**
     * @var ResolverInterface
     */
    private $jsResolver;

    /**
     * @var ResolverInterface
     */
    private $cssResolver;

    /**
     * @var string
     */
    private $scriptPosition;

    public function __construct(
        AssetBag $headers,
        AssetBag $footers,
        ResolverInterface $js,
        ResolverInterface $css,
        $scriptPosition
    ) {
        $this->headers = $headers;
        $this->footers = $footers;
        $this->jsResolver = $js;
        $this->cssResolver = $css;
        // @todo default to 'head' for BC in Core 1.x but default to 'foot' in Core-2.0
        $this->scriptPosition = isset($scriptPosition) && in_array($scriptPosition, ['head', 'foot']) ? $scriptPosition : 'head';
    }

    /**
     * Inject header assets into the head of the raw source of a page (before </head>)
     * Inject footer assets into the foot of the raw source of a page (before </body>)
     *
     * @param string $source
     * @param array $js
     * @param array $css
     * @return string
     */
    public function filter($source, $js = [], $css = [])
    {
        if (!empty($css)) {
            $this->cssResolver->getBag()->add($css);
        }
        if (!empty($js)) {
            $this->jsResolver->getBag()->add($js);
        }

        // compile and replace head
        $header = $this->cssResolver->compile();
        $header .= ($this->scriptPosition == 'head') ? $this->jsResolver->compile() : '';
        $header .= implode("\n", $this->headers->all()) . "\n";
//        $header .= trim(implode("\n", \PageUtil::getVar('header', [])) . "\n"); // @todo legacy - remove at Core-2.0
        if (strripos($source, '</head>')) {
            $source = str_replace('</head>', $header."\n</head>", $source);
        }

        // compile and replace foot
        $footer = ($this->scriptPosition == 'foot') ? $this->jsResolver->compile() : '';
        $footer .= trim(implode("\n", $this->footers->all()) . "\n");
//        $footer .= trim(implode("\n", \PageUtil::getVar('footer', [])) . "\n"); // @todo legacy - remove at Core-2.0
        if (false === empty($footer)) {
            $source = str_replace('</body>', $footer."\n</body>", $source);
        }

        return $source;
    }
}
