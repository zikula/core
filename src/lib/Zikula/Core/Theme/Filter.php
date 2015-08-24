<?php

namespace Zikula\Core\Theme;

use Zikula\Core\Theme\Asset\ResolverInterface;

class Filter
{
    private $pageVars;
    private $jsResolver;
    private $cssResolver;
    private $scriptPosition;

    public function __construct(ParameterBag $bag, ResolverInterface $js, ResolverInterface $css, $scriptPosition)
    {
        $this->pageVars = $bag;
        $this->jsResolver = $js;
        $this->cssResolver = $css;
        // @todo default to 'head' for BC in Core 1.x but default to 'foot' in Core-2.0
        $this->scriptPosition = isset($scriptPosition) && in_array($scriptPosition, array('head', 'foot')) ? $scriptPosition : 'head';
    }

    /**
     * Inject assets from pagevars into the head of the raw source of a page (before </head>)
     * Inject assets from pagevars into the foot of the raw source of a page (before </body>)
     *
     * @param string $source
     * @param array $js
     * @param array $css
     * @return string
     */
    public function filter($source, $js = array(), $css = array())
    {
        if (!empty($css)) {
            $this->cssResolver->getBag()->add($css);
        }
        if (!empty($js)) {
            $this->jsResolver->getBag()->add($js);
        }

        // compile and replace head
        $header = $this->cssResolver->compile();
        $header .= \JCSSUtil::getJSConfig(); // must be included before other scripts because it defines `Zikula` JS namespace
        $header .= ($this->scriptPosition == 'head') ? $this->jsResolver->compile(): '';
        $header .= implode("\n", $this->pageVars->get('header', array()))."\n";
        if (strripos($source, '</head>')) {
            $source = str_replace('</head>', $header."\n</head>", $source);
        }

        // compile and replace foot
        $footer = ($this->scriptPosition == 'foot') ? $this->jsResolver->compile(): '';
        $footer .= trim(implode("\n", $this->pageVars->get('footer', array()))."\n");
        if (false === empty($footer)) {
            $source = str_replace('</body>', $footer."\n</body>", $source);
        }

        return $source;
    }
}
