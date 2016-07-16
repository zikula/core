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
 * Zikula_View short urls outputfilter plugin.
 *
 * File:      outputfilter.shorturls.php
 * Type:      outputfilter
 * Name:      shorturls
 *
 * @param string      $source Output source
 * @param Zikula_View $view   Reference to Zikula_View instance
 *
 * @return string
 */
function smarty_outputfilter_shorturls($source, $view)
{
    // If you control the server, it is preferable for better performance to put rewrite rules
    // from the htaccess file into main configuration file, httpd.conf.

    $baseUrl = System::getBaseUrl();

    $prefix = '[(<[^>]*?)[\'"](?:'.$baseUrl.'|'.$baseUrl.')?(?:[./]{0,2})'; // Match local URLs in HTML tags, removes / and ./
    $in = ['[<([^>]+)\s(src|href|background|action)\s*=\s*((["\'])?)(?!http)(?!skype:)(?!xmpp:)(?!icq:)(?!mailto:)(?!tel:)(?!javascript:)(?!bitcoin:)(?!geo:)(?!im:)(?!irc:)(?!ircs:)(?!sms:)(?!ssh:)(?!urn:)(?!wtai:)(?!smsto:)(?!sms:)(?!sip:)(?!magnet:)(?!webcal:)(?!data:)(?![/"\'\s#]+)]Ui'];
    $out = ['<$1 $2=$3'.$baseUrl];

    // perform the replacement
    $source = preg_replace($in, $out, $source);

    // return the modified source
    return $source;
}
