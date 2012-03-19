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

ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
mb_regex_encoding('UTF-8');

if (!defined('LC_MESSAGES')) {
    define('LC_MESSAGES', 5);
}

include 'lib/StreamReader/Abstract.php';
include 'lib/StreamReader/String.php';
include 'lib/StreamReader/CachedFile.php';
include 'lib/i18n/ZGettext.php';
include 'lib/i18n/ZMO.php';

if (!isset($_GET['lang']) || count($_GET) < 2) {
    badRequest();
}

$lang = $_GET['lang'];
validate($lang);
if (!preg_match('#(^[a-z]{2,3}$)|(^[a-z]{2,3}-[a-z]{2,3}$)|(^[a-z]{2,3}-[a-z]{2,3}-[a-z]{2,3}$)#', $lang)) {
    badRequest();
}

$gettext = ZGettext::getInstance();
$gettext->setLocale(LC_MESSAGES, $lang);
$translations = array();
$translations[$lang] = array();

foreach ($_GET as $domain => $meta) {
    if ($domain == 'lang') {
        continue;
    }

    validate($domain);
    validate($meta);

    $p = explode('_', $domain);
    $type = $p[0];

    $m = explode('|', $meta);
    $name = $m[0]; // module name or system plugin name.
    $pluginName = isset($m[1]) ? $m[1] : '';

    switch ($type)
    {
        case 'module':
            $type = "modules/$name/";
            break;
        case 'theme':
            $type = "themes/$name/";
            break;
        case 'moduleplugin':
            $type = "modules/$name/plugins/$pluginName/";
            break;
        case 'systemplugin':
            $type = "plugins/$name/";
            break;
        case 'zikula':
            $type = '';
            break;
    }

    $override = "config/locale/$lang/LC_MESSAGES/{$domain}.mo";
    $path = "{$type}locale";
    if (file_exists($override)) {
        $path = 'config/locale';
    }
    
    $gettext->bindTextDomain($domain, $path);
    $gettext->bindTextDomainCodeset($domain, 'utf8');
    $reader = $gettext->getReader($domain);
    $reader->ngettext(1,2,3);
    $data = $reader->getCache_translations();
    unset($data['']);
    if ($data) {
        $translations[$lang][$domain] = array('plural-forms' => $reader->getPluralheader(), 'translations' => $data);
    }
}

header("HTTP/1.1 200");
header('Content-type: text/javascript;charset=UTF-8');
echo "if (typeof(Zikula) == 'undefined') { Zikula = {}; }\n Zikula._translations = ";
echo json_encode($translations);

/**
 * Validate get parameters, and die with bad request.
 *
 * @param string $value The value to validate.
 *
 * @return void
 */
function validate($value)
{
    if (preg_match('#[^a-zA-Z0-9_\|]#', $value)) {
        badRequest();
    }
}

/**
 * Send a 400 error and exit from a bad request.
 *
 * @return void
 */
function badRequest()
{
    header('HTTP/1.1 400');
    die('Bad request.');
}
