<?php

namespace Zikula\Core\Theme;

use Zikula\Core\Theme\Asset\ResolverInterface;

class Filter
{
    private $pageVars;
    private $jsResolver;
    private $cssResolver;

    public function __construct(ParameterBag $bag, ResolverInterface $js, ResolverInterface $css)
    {
        $this->pageVars = $bag;
        $this->jsResolver = $js;
        $this->cssResolver = $css;
    }

    /**
     * Inject stylesheets and headers from pagevars into the head of the raw source of a page (before </head>)
     * Inject javascripts and footers from pagevars into the foot of the raw source of a page (before </body>)
     *
     * @param string $source
     * @param array $js
     * @param array $css
     * @return string
     */
    public function filter($source, $js = array(), $css = array())
    {
        $header = implode("\n", $this->pageVars->get('header', array()))."\n";
        if (!empty($css)) {
            $this->cssResolver->getBag()->add($css);
        }
        $header .= $this->cssResolver->compile();

        $footer = trim(implode("\n", $this->pageVars->get('footer', array()))."\n");
        if (!empty($js)) {
            $this->jsResolver->getBag()->add($js);
        }
        $footer .= $this->jsResolver->compile();

        if (strripos($source, '</head>')) {
            $source = str_replace('</head>', $header."\n</head>", $source);
        }

        if (false === empty($footer)) {
            $source = str_replace('</body>', $footer."\n</body>", $source);
        }

        return $source;
    }
}
