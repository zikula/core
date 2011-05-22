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
 * Zikula_View short urls outputfilter plugin.
 *
 * File:      outputfilter.shorturls.php
 * Type:      outputfilter
 * Name:      shorturls
 *
 * @param string      $source Output source.
 * @param Zikula_View $view   Reference to Zikula_View instance.
 *
 * @return string
 */
function smarty_outputfilter_shorturls($source, $view)
{
    // If you control the server, it is preferable for better performance to put rewrite rules
    // from the htaccess file into main configuration file, httpd.conf.

    $baseurl = System::getBaseUrl();

    $prefix = '[(<[^>]*?)[\'"](?:'.$baseurl.'|'.$baseurl.')?(?:[./]{0,2})'; // Match local URLs in HTML tags, removes / and ./
    $in = array('[<([^>]+)\s(src|href|background|action)\s*=\s*((["\'])?)(?!http)(?!skype)(?!xmpp)(?!icq)(?!mailto)(?!javascript:)(?![/"\'\s#]+)]Ui');
    $out = array('<$1 $2=$3'.$baseurl);

    // perform the replacement
    $source = preg_replace($in, $out, $source);

    // return the modified source
    return $source;
}
