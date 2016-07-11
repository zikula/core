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
 * Plugin to replace URLs found within a string into HTML links.
 *
 * @param string $text Text to apply modifier on.
 *
 * @return string
 */
function smarty_modifier_activatelinks($text)
{
    $text = preg_replace("'(\w+)://([\w\+\-\@\=\?\.\%\/\:\&\;~\|]+)(\.)?'", "<a href=\"\\1://\\2\">\\1://\\2</a>", $text);
    $text = preg_replace("'(\s+)www\.([\w\+\-\@\=\?\.\%\/\:\&\;~\|]+)(\.\s|\s)'", "\\1<a href=\"http://www.\\2\">www.\\2</a>\\3", $text);

    return $text;
}
