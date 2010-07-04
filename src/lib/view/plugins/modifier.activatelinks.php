<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
    $text = preg_replace("'(\s+)www\.([\w\+\-\@\=\?\.\%\/\:\&\;~\|]+)(\.\s|\s)'", "\\1<a href=\"http://www.\\2\">www.\\2</a>\\3" , $text);

    return $text;
}
