<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

if (!defined('LC_MESSAGES')) {
    define('LC_MESSAGES', 5);
}

include 'lib/StreamReader/Abstract.php';
include 'lib/StreamReader/String.php';
include 'lib/StreamReader/CachedFile.php';
include 'lib/i18n/ZGettext.php';
include 'lib/i18n/ZMO.php';

$domain = filter_input(INPUT_GET, 'domain', FILTER_SANITIZE_STRING);
$lang = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING);
$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
$modplugin = filter_input(INPUT_GET, 'modplugin', FILTER_SANITIZE_STRING);
$p = explode('_', $domain);
$type = $p[0];

switch ($type)
{
    case 'module':
        $type = "modules/$name/";
        break;
    case 'theme':
        $type = "themes/$name/";
        break;
    case 'moduleplugin':
        $type = "modules/$name/plugins/$modplugin/";
        break;
    case 'systemplugin':
        $type = "plugins/$name/";
        break;
    case 'zikula':
        $type = '';
        break;
}

$override = "config/$lang/LC_MESSAGES/{$domain}.mo";

if (file_exists($override)) {
    $path = 'config/locale';
} else {
    $path = "{$type}locale";
}

$gettext = ZGettext::getInstance();
$gettext->setLocale(LC_MESSAGES, $lang);
$gettext->bindTextDomain($domain, $path);
$gettext->bindTextDomainCodeset($domain, 'utf-8');
$reader = $gettext->getReader($domain);
$reader->ngettext(1,2,3);
$data = $reader->getCache_translations();
unset($data['']);
$array = array('plural-forms' => $reader->getPluralheader(), 'translations' => $data);

if ($data) {
    header("HTTP/1.1 200");
    header('Content-type: application/json');
    echo json_encode($array);
} else {
    header("HTTP/1.1 404");
    echo "Not Found.";
}

