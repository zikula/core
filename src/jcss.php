<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
define('ACCESS_ADMIN', 1);
include 'config/config.php';
include 'lib/util/FormUtil.php';
global $ZConfig;
$f = FormUtil::getPassedValue('f', null, 'GET');

if (!isset($f)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// clean $f
$f = preg_replace('`/`', '', $f);

// set full path to the file
$f = $ZConfig['System']['temp'] . '/Theme_cache/' . $f;

if (!is_readable($f)) {
    header('HTTP/1.0 404 Not Found');
    die('ERROR: Requested file not readable.');
}

// child lock
$signingKey = md5($ZConfig['DBInfo']['default']['dsn']);

$contents = file_get_contents($f);
if (!is_serialized($contents)) {
    header('HTTP/1.0 404 Not Found');
    die('ERROR: Corrupted file.');
}

$dataArray = unserialize($contents);
if (!isset($dataArray['contents']) || !isset($dataArray['ctype']) || !isset($dataArray['lifetime']) || !isset($dataArray['gz']) || !isset($dataArray['signature'])) {
    header('HTTP/1.0 404 Not Found');
    die('ERROR: Invalid data.');
}

// check signature
if (md5($dataArray['contents'] . $dataArray['ctype'] . $dataArray['lifetime'] . $dataArray['gz'] . $signingKey) != $dataArray['signature']) {
    header('HTTP/1.0 404 Not Found');
    die('ERROR: File has been altered.');
}

// gz handlers if requested
if ($dataArray['gz']) {
    ini_set('zlib.output_handler', '');
    ini_set('zlib.output_compression', 1);
}

header("Content-type: $dataArray[ctype]");
header('Cache-Control: must-revalidate');
header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $dataArray['lifetime']) . ' GMT');
echo $dataArray['contents'];
exit;

/**
 * Check if a string is serialized.
 *
 * This function check if a string is serialized.
 *
 * @param string $string String to check.
 *
 * @return boolean True if it's serialized, false if it's not.
 */
function is_serialized($string)
{
    return ($string == 'b:0;' ? true : (bool)@unserialize($string));
}

/**
 * Un-quotes a quoted string.
 *
 * This function Un-quotes a quoted string. Return is void, $value is
 * un-quoted by reference.
 *
 * @param string &$value String to un-quotes.
 *
 * @return void
 */
function pnStripslashes(&$value)
{
    if (empty($value))
        return;

    if (!is_array($value)) {
        $value = stripslashes($value);
    } else {
        array_walk($value, 'pnStripslashes');
    }
}

/**
 * Class SecurityUtil fake.
 *
 * This is a  fake SecurityUtil class
 *
 * @package zikula
 */
class SecurityUtil
{

    /**
     * Fake checkPermission function.
     *
     * This is a fake function.
     *
     * @return return true.
     */
    function checkPermission()
    {
        return true;
    }
}
