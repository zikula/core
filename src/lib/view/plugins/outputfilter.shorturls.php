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
 * @param string $source  Output source.
 * @param Zikula_View $view Reference to Zikula_View instance.
 *
 * @return string
 */
function smarty_outputfilter_shorturls($source, $view)
{
    // If you control the server, it is preferable for better performance to put rewrite rules
    // from the htaccess file into main configuration file, httpd.conf.

    // get the site's base uri, eg /nuke/ for root-relative links
    $baseurl = System::getBaseUrl();
    $type    = FormUtil::getPassedValue('type', 'user', 'GETPOST');

    if (System::getVar('shorturlstype') == 0) {
        $prefix = '[(<[^>]*?)[\'"](?:'.$baseurl.'|'.$baseurl.')?(?:[./]{0,2})'; // Match local URLs in HTML tags, removes / and ./
        // '|"(?:'.$baseurl.')?'; <[^>]*?[\'"]

        // (?i) means case insensitive; \w='word' character; \d=digit; (amp;)? means content of brackets optional; (?:catid=)? means optional and won't capture string for backreferences
        // [#-~]+ matches ASCII# 35(#)-126(~), ie all (English) upper & lower case letters, numbers and special characters like #$!@+%^&*()~ etc, ie all but space (32), DEl, and other specxial non-printing codes like backspace, newline, and tab
        // [0-9A-Za-zÀ-ÖØ-öø-ÿ_]+ matches all international letters
        $in = array(
            '[<([^>]+)\s(src|href|background|action)\s*=\s*((["\'])?)(?!http)(?!skype)(?!xmpp)(?!icq)(?!mailto)(?!javascript:)(?![/"\'\s#]+)]Ui'
        );

        $out = array(
            '<$1 $2=$3'.$baseurl
        );
    } else if ($type !== 'admin' && !stristr(System::serverGetVar('QUERY_STRING'), 'admin')) {
        // Credits to
        // ColdRolledSteel: for creating this file and the rewrite rules / per aver creato questo file e le regole di riscrittura
        // msandersen: for tweaking this file and the rewrite rules / per aver aggiornato questo file e le regole di riscrittura
        // CHTEKK: adaptation for eNvolution, writing/rewriting of many rules, italian translation and on/off variable / adattazione ad eNvolution, scrittura/riscrittura di diverse regole, traduzione italiana e variabile on/off
        //
        // If you control the server, it is preferable for better performance to put rewrite rules
        // from the .htaccess file into main configuration file, httpd.conf.

        $extension = System::getVar('shorturlsext');
        $prefix = '|"(?:'.$baseurl.')?';
        $entrypoint = System::getVar('entrypoint', 'index.php');

        // (?i) means case insensitive; \w='word' character; \d=digit; (amp;)? means optional; (?:catid=)? means optional and won't capture string for backreferences
        $in = array(
            $prefix . '\?theme=([\w\d\.\:\_\/]+)"|',
            $prefix . $entrypoint . '"|',
            $prefix . 'user.php"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=main"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=main[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=main&(?:amp;)?lang=([a-z-]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=main&(?:amp;)?lang=([a-z-]+)[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)[#]([\w\d]*)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)"[#]([\w\d]*)|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)"|',
            $prefix . $entrypoint . '\?module=([\w\d\.\:\_\/]+)&(?:amp;)?func=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?([\w\d\.\:\_\/]+)=([\w\d\.\:\_\/]+)&(?:amp;)?lang=([a-z-]+)"[#]([\w\d]*)|',
        );

        $out = array(
            '"previewtheme-$1.'.$extension.'"',
            '"index.'.$extension.'"',
            '"user.'.$extension.'"',
            '"module-$1.'.$extension.'"',
            '"module-$1.'.$extension.'#$2"',
            '"module-$1-main-lang-$2.'.$extension.'"',
            '"module-$1-main-lang-$2.'.$extension.'#$3"',
            '"module-$1.'.$extension.'"',
            '"module-$1.'.$extension.'#$2"',
            '"module-$1-main-lang-$2.'.$extension.'"',
            '"module-$1-main-lang-$2.'.$extension.'#$3"',
            '"module-$1-$2.'.$extension.'"',
            '"module-$1-$2.'.$extension.'#$3"',
            '"module-$1-$2-lang-$3.'.$extension.'"',
            '"module-$1-$2-lang-$3.'.$extension.'#$3"',
            '"module-$1-$2-$3-$4.'.$extension.'"',
            '"module-$1-$2-$3-$4.'.$extension.'#$5"',
            '"module-$1-$2-$3-$4-lang-$5.'.$extension.'"',
            '"module-$1-$2-$3-$4-lang-$5.'.$extension.'#$6"',
            '"module-$1-$2-$3-$4-$5-$6.'.$extension.'"',
            '"module-$1-$2-$3-$4-$5-$6.'.$extension.'#$7"',
            '"module-$1-$2-$3-$4-$5-$6-lang-$7.'.$extension.'"',
            '"module-$1-$2-$3-$4-$5-$6-lang-$7.'.$extension.'#$8"',
            '"module-$1-$2-$3-$4-$5-$6-$7-$8.'.$extension.'"',
            '"module-$1-$2-$3-$4-$5-$6-$7-$8.'.$extension.'#$9"',
            '"module-$1-$2-$3-$4-$5-$6-$7-$8-lang-$9.'.$extension.'"',
            '"module-$1-$2-$3-$4-$5-$6-$7-$8-lang-$9.'.$extension.'#$10"',
        );
    } else {
        $in = array();
        $out = array();
    }
    // perform the replacement
    $source = preg_replace($in, $out, $source);

    // return the modified source
    return $source;
}
