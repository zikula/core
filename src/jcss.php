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

include 'lib/bootstrap.php';
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
global $ZConfig;
$f = (isset($_GET['f']) ? filter_var($_GET['f'], FILTER_SANITIZE_STRING) : false);

if (!$f) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// clean $f
$f = preg_replace('`/`', '', $f);

// set full path to the file
$f = $ZConfig['System']['temp'] . '/Theme_cache/' . $f;

if (!is_readable($f)) {
    header('HTTP/1.0 400 Bad request');
    die('ERROR: Requested file not readable.');
}

// child lock
$signingKey = md5(serialize($ZConfig['DBInfo']['databases']['default']));

$contents = file_get_contents($f);
if (!DataUtil::is_serialized($contents, false)) {
    header('HTTP/1.0 500 Internal error');
    die('ERROR: Corrupted file.');
}

$dataArray = unserialize($contents);
if (!isset($dataArray['contents']) || !isset($dataArray['ctype']) || !isset($dataArray['lifetime']) || !isset($dataArray['gz']) || !isset($dataArray['signature'])) {
    header('HTTP/1.0 500 Interal error');
    die('ERROR: Invalid data.');
}

// check signature
if (md5($dataArray['contents'] . $dataArray['ctype'] . $dataArray['lifetime'] . $dataArray['gz'] . $signingKey) != $dataArray['signature']) {
    header('HTTP/1.0 500 Interal error');
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
 * Class SecurityUtil fake.
 *
 * This is a fake SecurityUtil class.
 */
class SecurityUtil
{

    /**
     * Fake checkPermission function.
     *
     * This is a fake function.
     *
     * @return boolean true.
     */
    function checkPermission()
    {
        return true;
    }
}
