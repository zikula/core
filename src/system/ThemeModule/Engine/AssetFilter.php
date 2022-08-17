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

namespace Zikula\ThemeModule\Engine;

use Zikula\ThemeModule\Engine\Asset\ResolverInterface;

/**
 * Class AssetFilter
 *
 * This class resolves, compiles and renders all page assets and adds them to the outgoing source content
 * Recently, the accepted practice for placement of javascript has changed from header to footer. Placement in this
 * class is determined by the `scriptPosition` parameter in `config/services.yaml`. Since Core-2.0 this defaults to
 * footer. Scripts must be written to accommodate this.
 */
class AssetFilter
{
    private string $scriptPosition;

    public function __construct(
        private readonly AssetBag $headers,
        private readonly AssetBag $footers,
        private readonly ResolverInterface $jsResolver,
        private readonly ResolverInterface $cssResolver,
        string $scriptPosition
    ) {
        $this->scriptPosition = isset($scriptPosition) && in_array($scriptPosition, ['head', 'foot']) ? $scriptPosition : 'foot';
    }

    /**
     * Inject header assets into the head of the raw source of a page (before </head>).
     * Inject footer assets into the foot of the raw source of a page (before </body>).
     */
    public function filter(string $source, array $js = [], array $css = []): string
    {
        if (!empty($css)) {
            $this->cssResolver->getBag()->add($css);
        }
        if (!empty($js)) {
            $this->jsResolver->getBag()->add($js);
        }

        // compile and replace head
        $header = $this->cssResolver->compile();
        $header .= implode("\n", $this->headers->all()) . "\n";
        $header .= ('head' === $this->scriptPosition) ? $this->jsResolver->compile() : '';
        if (mb_strripos($source, '</head>')) {
            $source = str_replace('</head>', $header . "\n</head>", $source);
        }

        // compile and replace foot
        $footer = ('foot' === $this->scriptPosition) ? $this->jsResolver->compile() : '';
        $footer .= trim(implode("\n", $this->footers->all()) . "\n");
        if (false === empty($footer)) {
            $source = str_replace('</body>', $footer . "\n</body>", $source);
        }

        return $source;
    }
}
