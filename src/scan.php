<?php

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Filter\Yui\JsCompressorFilter as YuiCompressorFilter;

$js = new AssetCollection(array(
                               new FileAsset(__DIR__.'/jquery.js'),
                               new FileAsset(__DIR__.'/application.js'),
                          ), array(
                                  new YuiCompressorFilter('/path/to/yuicompressor.jar'),
                             ));

header('Content-Type: application/js');
echo $js->dump();

exit;

use Doctrine\ORM\EntityManager;

$e=array();


class Ses implements \SessionHandlerInterface
{
    function open($a, $b) { echo "O";}
    function close() { echo "C";}
    function read($a) { echo "R";}
    function write($a, $b) { echo "W";}
    function destroy($a) { echo "D";}
    function gc($a) { echo "G";}
}
$s= new Ses();
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1);
session_set_save_handler($s);
session_start();


exit;
$source = <<<EOF
<link rel="stylesheet" href="web/font-awesome/css/font-awesome.min.css" type="text/css" />
<link rel="stylesheet" href="web/bootstrap/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="style/core.css" type="text/css" />
<link rel="stylesheet" href="style/legacy.css" type="text/css" />
<link rel="stylesheet" href="web/bootstrap/css/bootstrap-theme.min.css" type="text/css" />
<link rel="stylesheet" href="themes/Zikula/Theme/Andreas08Theme/Resources/public/css/fluid960gs/reset.css" type="text/css" />
<link rel="stylesheet" href="themes/Zikula/Theme/Andreas08Theme/Resources/public/css/fluid960gs/grid.css" type="text/css" />
<link rel="stylesheet" href="themes/Zikula/Theme/Andreas08Theme/Resources/public/css/style.css" type="text/css" />
<link rel="stylesheet" href="system/Zikula/Module/BlocksModule/Resources/public/css/style.css" type="text/css" />
<link rel="stylesheet" href="system/Zikula/Module/BlocksModule/Resources/public/css/extmenu.css" type="text/css" />
<link rel="stylesheet" href="system/Zikula/Module/SearchModule/Resources/public/css/style.css" type="text/css" />
<link rel="stylesheet" href="system/Zikula/Module/UsersModule/Resources/public/css/style.css" type="text/css" />
EOF;

$source = preg_replace_callback('#(href=|src=){1}("|\'){1}([a-zA-Z0-9/\.\-_]+)("|\'){1}#', '_smarty_outputfilter_asseturls', $source);
echo "<blockquote>";
echo $source;
echo "</blockquote>";
function _smarty_outputfilter_asseturls($m)
{
    $url = $m[3]."***";
    return "$m[1]$m[2]{$url}$m[4]";
}


exit;

include 'lib/util/System.php';
include 'config/personal_config.php';
// target / input

var_dump(System::isLegacyMode('1.3.0'));
exit;


//hex input must be in uppercase, with no leading 0x
class Base53
{
    const ADDRESSVERSION = "00"; //this is a hex byte
    function decodeHex($hex)
    {
        $hex = strtoupper($hex);
        $chars = "0123456789ABCDEF";
        $return = "0";
        for ($i = 0; $i < strlen($hex); $i++) {
            $current = (string) strpos($chars, $hex[$i]);
            $return = (string) bcmul($return, "16", 0);
            $return = (string) bcadd($return, $current, 0);
        }

        return $return;
    }

    function encodeHex($dec)
    {
        $chars = "0123456789ABCDEF";
        $return = "";
        while (bccomp($dec, 0) == 1) {
            $dv = (string) bcdiv($dec, "16", 0);
            $rem = (integer) bcmod($dec, "16");
            $dec = $dv;
            $return = $return . $chars[$rem];
        }

        return strrev($return);
    }

    function decodeBase58($base58)
    {
        $origbase58 = $base58;
        $chars = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
        $return = "0";
        for ($i = 0; $i < strlen($base58); $i++) {
            $current = (string) strpos($chars, $base58[$i]);
            $return = (string) bcmul($return, "58", 0);
            $return = (string) bcadd($return, $current, 0);
        }
        $return = self::encodeHex($return);
        //leading zeros
        for ($i = 0; $i < strlen($origbase58) && $origbase58[$i] == "1"; $i++) {
            $return = "00" . $return;
        }
        if (strlen($return) % 2 != 0) {
            $return = "0" . $return;
        }

        return $return;
    }

    function encodeBase58($hex)
    {
        if (strlen($hex) % 2 != 0) {
            die("encodeBase58: uneven number of hex characters");
        }
        $orighex = $hex;
        $chars = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
        $hex = self::decodeHex($hex);
        $return = "";
        while (bccomp($hex, 0) == 1) {
            $dv = (string) bcdiv($hex, "58", 0);
            $rem = (integer) bcmod($hex, "58");
            $hex = $dv;
            $return = $return . $chars[$rem];
        }
        $return = strrev($return);
        //leading zeros
        for ($i = 0; $i < strlen($orighex) && substr($orighex, $i, 2) == "00"; $i += 2) {
            $return = "1" . $return;
        }

        return $return;
    }

    function hash160ToAddress($hash160, $addressversion = self::ADDRESSVERSION)
    {
        $hash160 = $addressversion . $hash160;
        $check = pack("H*", $hash160);
        $check = hash("sha256", hash("sha256", $check, true));
        $check = substr($check, 0, 8);
        $hash160 = strtoupper($hash160 . $check);

        return self::encodeBase58($hash160);
    }

    function addressToHash160($addr)
    {
        $addr = self::decodeBase58($addr);
        $addr = substr($addr, 2, strlen($addr) - 10);

        return $addr;
    }

    function checkAddress($addr, $addressversion = ADDRESSVERSION)
    {
        $addr = self::decodeBase58($addr);
        if (strlen($addr) != 50) {
            return false;
        }
        $version = substr($addr, 0, 2);
        if (hexdec($version) > hexdec($addressversion)) {
            return false;
        }
        $check = substr($addr, 0, strlen($addr) - 8);
        $check = pack("H*", $check);
        $check = strtoupper(hash("sha256", hash("sha256", $check, true)));
        $check = substr($check, 0, 8);

        return $check == substr($addr, strlen($addr) - 8);
    }

    function hash160($data)
    {
        $data = pack("H*", $data);

        return strtoupper(hash("ripemd160", hash("sha256", $data, true)));
    }

    function pubKeyToAddress($pubkey)
    {
        return self::hash160ToAddress(hash160($pubkey));
    }

    function remove0x($string)
    {
        if (substr($string, 0, 2) == "0x" || substr($string, 0, 2) == "0X") {
            $string = substr($string, 2);
        }

        return $string;
    }
}