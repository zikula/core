<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Forward compatibility: Symfony uses {{ asset('path/to') }}
 *
 * This fixes relative URLs for Symfony so Smarty templates/themes
 * will work with Twig output.
 *
 * @param $source
 * @param $view
 *
 * @deprecated this is not to be considered API
 *
 * @return mixed
 */
function smarty_outputfilter_asseturls($source, $view)
{
    $source = preg_replace_callback('#(href=|src=){1}("|\'){1}([a-zA-Z0-9\/\.\-_]+)("|\'){1}#', '_smarty_outputfilter_asseturls', $source);

    return $source;
}

function _smarty_outputfilter_asseturls($m)
{
    $url = $m[3];
    if ($url[0] !== '/') {
        $url = $GLOBALS['__request']->getBasePath().'/'.$url;
    }

    return "$m[1]$m[2]{$url}$m[4]";
}
