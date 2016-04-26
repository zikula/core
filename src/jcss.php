<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include 'lib/bootstrap.php';

/**
 * mbstring.internal_encoding
 *
 * This feature has been deprecated as of PHP 5.6.0. Relying on this feature is highly discouraged.
 * PHP 5.6 and later users should leave this empty and set default_charset instead.
 *
 * @link http://php.net/manual/en/mbstring.configuration.php#ini.mbstring.internal-encoding
 */
if (version_compare(\PHP_VERSION, '5.6.0', '<')) {
    ini_set('mbstring.internal_encoding', 'UTF-8');
}

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
    public function checkPermission()
    {
        return true;
    }
}
