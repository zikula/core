<?php

namespace Zikula\Core\Theme;

use Zikula\Core\Theme\ParameterBag;

class Filter
{
    private $pageVars;

    public function __construct(ParameterBag $bag)
    {
        $this->pageVars = $bag;
    }

    public function filter($source, $js, $css)
    {
        $header = implode("\n", $this->pageVars->get('header', array()))."\n";
        if ($css) {
            $header .= trim($css."\n");
        }

        if ($js) {
            $header .= trim($js."\n");
        }

        $footer = trim(implode("\n", $this->pageVars->get('footer', array()))."\n");

        if (strripos($source, '</head>')) {
            $source = str_replace('</head>', $header."\n</head>", $source);
        }

        if (false === empty($footer)) {
            $source = str_replace('</body>', $footer, $source);
        }

        return $source;
    }
}
