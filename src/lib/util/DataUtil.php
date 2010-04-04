<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * DataUtil
 *
 * @package Zikula_Core
 * @subpackage DataUtil
 */
class DataUtil
{
    /**
     * Clean a variable, remove slashes. This method is recursive array safe.
     *
     * @param var        The variable to clean
     *
     * @return The formatted variable
     */
    public static function cleanVar($var)
    {
        if (!get_magic_quotes_gpc()) {
            return $var;
        }

        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::cleanVar($v);
            }
        } else {
            pnStripslashes($var);
        }

        return $var;
    }

    /**
     * Decode a character a previously encoded character
     *
     * @param value      The value we wish to encode
     *
     * @return The decoded value
     */
    public static function decode($value)
    {
        return base64_decode($value);
    }

    /** Take a name-value-pair string and convert it to an associative array, optionally urldecoding the response. 
      * 
      * @param nvpstr       Name-value-pair String.
      * @param separator    Separator used in the NVP string
      * @param urldecode    Whether to urldecode the NVP fields
      *
      * @return assoc is associative array.
     */
    public static function decodeNVP ($nvpstr, $separator='&', $urldecode=true)                                                                                   
    {                                                                                                                                               
        $assoc = array();                                                                                                                           
        $items = explode ($separator, $nvpstr);                                                                                                     
        foreach ($items as $item) {                                                                                                                 
            $fields = explode ('=', $item);                                                                                                         
            $key    = $urldecode ? urldecode($fields[0]) : $fields[0];                                                                              
            $value  = $urldecode ? urldecode($fields[1]) : $fields[1];                                                                              
            $assoc[$key] = $value;                                                                                                                  
        } 

        return $assoc;
    }         

    /**
     * Decrypt the given value using the mcrypt library function. If the mcrypt
     * functions do not exist, we fallback to the RC4 implementation which is
     * shipped with Zikula.
     *
     * @param value      The value we wish to decrypt
     * @param key        The encryption key to use (optional) (default=null)
     * @param alg        The encryption algirthm to use (only used with mcrypt functions) (optional) (default=null, signifies MCRYPT_RIJNDAEL_128)
     * @param encoded    Whether or not the value is base64 encoded (optional) (default=true)
     *
     * @return The decrypted value
     */
    public static function decrypt($value, $key = null, $alg = null, $encoded = true)
    {
        $res = false;
        $key = ($key ? $key : 'ZikulaEncryptionKey');
        $val = ($encoded ? self::decode($value) : $value);

        if (function_exists('mcrypt_create_iv') && function_exists('mcrypt_decrypt')) {
            $alg = ($alg ? $alg : MCRYPT_RIJNDAEL_128);
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($alg, MCRYPT_MODE_ECB), crc32($key));
            $res = mcrypt_decrypt($alg, $key, $val, MCRYPT_MODE_CBC);
        } else {
            Loader::requireOnce('lib/vendor/encryption/rc4crypt.class.php');
            $res = rc4crypt::decrypt($key, $val);
        }

        return $res;
    }

    /**
     * Encode a character sting such that it's 8-bit clean. It maps to base64_encode().
     *
     * @param value      The value we wish to encode
     *
     * @return The encoded value
     */
    public static function encode($value)
    {
        return base64_encode($value);
    }

    /** Take a key and value and encode them into an NVP-string entity
      * 
      * @param key          The key to encode
      * @param value        The value to encode
      * @param separator    The Separator to use in the NVP string
      * @param includeEmpty Whether to also include empty values
      *
      * @return string-encoded NVP or an empty string
     */
    public static function encodeNVP ($key, $value, $separator='&', $includeEmpty=true) 
    {                                                                                                                                                           
        if (!$key) {                                                                                                                                           
            return LogUtil::registerError ('Invalid NVP key received');                                                                                        
        }                                                                                                                                                       

        if ($includeEmpty || ($value != null && strlen($value) > 1)) {                                                                                          
            return ("&".urlencode($key) ."=" .urlencode($value));                                                                                              
        }                                                                                                                                                       

        return '';
    }

    /** Take an array and encode it as a NVP string
      * 
      * @param nvap         The array of name-value paris
      * @param separator    The Separator to use in the NVP string
      * @param includeEmpty Whether to also include empty values
      *
      * @return string-encoded NVP or an empty string
     */
    public static function encodeNVPArray ($nvps, $separator='&', $includeEmpty=true)
    {
        $str = '';

        foreach ($nvps as $k=>$v) {
            $str .= WebstoreUtil::encodeNVP ($k, $v, $separator, $includeEmpty);
        }

        return $str;
    }

    /**
     * Encrypt the given value using the mcrypt library function. If the mcrypt
     * functions do not exist, we fallback to the RC4 implementation which is
     * shipped with Zikula.
     *
     * @param value      The value we wish to decrypt
     * @param key        The encryption key to use (optional) (default=null)
     * @param alg        The encryption algirthm to use (only used with mcrypt functions) (optional) (default=null, signifies MCRYPT_RIJNDAEL_128)
     * @param encoded    Whether or not the value is base64 encoded (optional) (default=true)
     *
     * @return The encrypted value
     */
    public static function encrypt($value, $key = null, $alg = null, $encoded = true)
    {
        $res = false;
        $key = ($key ? $key : 'ZikulaEncryptionKey');

        if (function_exists('mcrypt_create_iv') && function_exists('mcrypt_decrypt')) {
            $alg = ($alg ? $alg : MCRYPT_RIJNDAEL_128);
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($alg, MCRYPT_MODE_ECB), crc32($key));
            $res = mcrypt_encrypt($alg, $key, $value, MCRYPT_MODE_CBC);
        } else {
            Loader::requireOnce('lib/vendor/encryption/rc4crypt.class.php');
            $res = rc4crypt::encrypt($key, $value);
        }

        return ($encoded && $res ? self::encode($res) : $res);
    }

    /**
     * Format a variable for display. This method is recursive array safe.
     *
     * @param var        The variable to format
     *
     * @return The formatted variable
     */
    public static function formatForDisplay($var)
    {
        // This search and replace finds the text 'x@y' and replaces
        // it with HTML entities, this provides protection against
        // email harvesters
        static $search = array('/(.)@(.)/se');

        static $replace = array('"&#" .
                                sprintf("%03d", ord("\\1")) .
                                ";&#064;&#" .
                                sprintf("%03d", ord("\\2")) . ";";');

        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::formatForDisplay($v);
            }
        } else {
            $var = htmlspecialchars((string) $var);
            $var = preg_replace($search, $replace, $var);
        }

        return $var;
    }

    /**
     * Format a variable for HTML display. This method is recursive array safe.
     *
     * @param var        The variable to format
     *
     * @return The formatted variable
     */
    public static function formatForDisplayHTML($var)
    {
        // This search and replace finds the text 'x@y' and replaces
        // it with HTML entities, this provides protection against
        // email harvesters
        //
        // Note that the use of \024 and \022 are needed to ensure that
        // this does not break HTML tags that might be around either
        // the username or the domain name
        static $search = array(
            '/([^\024])@([^\022])/se');

        static $replace = array('"&#" .
                                sprintf("%03d", ord("\\1")) .
                                ";&#064;&#" .
                                sprintf("%03d", ord("\\2")) . ";";');

        static $allowedtags = null;
        static $outputfilter;

        if (!isset($allowedtags)) {
            $allowedHTML = array();
            $allowableHTML = pnConfigGetVar('AllowableHTML');
            if (is_array($allowableHTML)) {
                foreach ($allowableHTML as $k => $v) {
                    if ($k == '!--') {
                        if ($v != 0) {
                            $allowedHTML[] = "$k.*?--";
                        }
                    } else {
                        switch ($v) {
                            case 0:
                                break;
                            case 1:
                                $allowedHTML[] = "/?$k\s*/?";
                                break;
                            case 2:
                                // intelligent regex to deal with > in parameters, bug #1782 credits to jln
                                $allowedHTML[] = "/?\s*$k" . "(\s+[\w:]+\s*=\s*(\"[^\"]*\"|'[^']*'))*" . '\s*/?';
                                break;
                        }
                    }
                }
            }

            if (count($allowedHTML) > 0) {
                // 2nd part of bugfix #1782
                $allowedtags = '~<\s*(' . join('|', $allowedHTML) . ')\s*>~is';
            } else {
                $allowedtags = '';
            }
        }

        if (!isset($outputfilter)) {
            if (pnModAvailable('SecurityCenter') && !defined('_ZINSTALLVER')) {
                $outputfilter = pnConfigGetVar('outputfilter');
            } else {
                $outputfilter = 0;
            }
        }

        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::formatForDisplayHTML($v);
            }
        } else {
            // Run additional filters
            if ($outputfilter > 0) {
                $var = pnModAPIFunc('SecurityCenter', 'user', 'secureoutput', array('var' => $var, 'filter' => $outputfilter));
            }

            // Preparse var to mark the HTML that we want
            if (!empty($allowedtags)) {
                $var = preg_replace($allowedtags, "\022\\1\024", $var);
            }

            // Encode email addresses
            $var = preg_replace($search, $replace, $var);

            // Fix html entities
            $var = htmlspecialchars($var);

            // Fix the HTML that we want
            $var = preg_replace_callback('#\022([^\024]*)\024#', create_function('$m', 'return DataUtil::formatForDisplayHTML_callback($m);'), $var);

            // Fix entities if required
            if (pnConfigGetVar('htmlentities')) {
                $var = preg_replace('/&amp;([a-z#0-9]+);/i', "&\\1;", $var);
            }
        }

        return $var;
    }

    /**
     * formatForDisplayHTML callback
     *
     * @access private
     * @param array $m
     * @return string|void on empty
     */
    public static function formatForDisplayHTML_callback($m)
    {
        if (!$m) {
            return;
        }
        return '<' . strtr($m[1], array('&gt;' => '>', '&lt;' => '<', '&quot;' => '"'/*, '&amp;' => '&'*/)) . '>';
    }


    /**
     * Format a variable for DB-storage. This method is recursive array safe.
     *
     * @param var        The variable to format
     *
     * @return The formatted variable
     */
    public static function formatForStore($var)
    {
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::formatForStore($v);
            }
        } else {
            $dbType = DBConnectionStack::getConnectionDBType();
            if ($dbType == 'mssql' || $dbType == 'oci8' || $dbType == 'oracle') {
                $var = str_replace("'", "''", $var);
            } else
                $var = addslashes($var);
        }

        return $var;
    }

    /**
     * Format a variable for operating-system usage. This method is recursive array safe.
     *
     * @param var        The variable to format
     * @param absolute   Allow absolute paths (default=false) (optional)
     *
     * @return The formatted variable
     */
    public static function formatForOS($var, $absolute = false)
    {
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::formatForOS($v);
            }
        } else {
            static $cached;
            if ($cached == null) {
                $cached = array();
            }

            if (isset($cached[$var])) {
                return $cached[$var];
            }
            $orgVar = $var;

            $clean_array = array();

            //if we're supporting absolute paths and the first charater is a slash and , then
            //an absolute path is passed
            $absolutepathused = ($absolute && substr($var, 0, 1) == '/');

            // Split the path at possible path delimiters.
            // Setting PREG_SPLIT_NOEMPTY eliminates double delimiters on the fly.
            $dirty_array = preg_split('#[:/\\\\]#', $var, -1, PREG_SPLIT_NO_EMPTY);

            // now walk the path and do the relevant things
            foreach ($dirty_array as $current) {
                if ($current == '.') {
                    // current path element is a dot, so we don't do anything
                } elseif ($current == '..') {
                    // current path element is .., so we remove the last path in case of relative paths
                    if (!$absolutepathused) {
                        array_pop($clean_array);
                    }
                } else {
                    // current path element is valid, so we add it to the path
                    $clean_array[] = $current;
                }
            }

            // build the path
            // should we use DIRECTORY_SEPARATOR here?
            $var = implode('/', $clean_array);
            //if an absolute path was passed to the function, we need to make it absolute again
            if ($absolutepathused) {
                $var = '/' . $var;
            }

            // Prepare var
            // needed for magic_quotes_runtime = 0
            $var = addslashes($var);

            $cached[$orgVar] = $var;
        }

        return $var;
    }

    public static function formatForURL($var)
    {
        return self::formatPermalink($var);
    }

    public static function formatPermalink($var)
    {
        static $permalinksseparator;
        if (!isset($permalinksseparator)) {
            $permalinksseparator = pnConfigGetVar('shorturlsseparator');
        }
        $var = strip_tags($var);
        $var = preg_replace("/&[#a-zA-Z0-9]+;|\?/", '', $var); // remove &....; and ?
        $var = strtr($var, ' ', $permalinksseparator); //words separation


        if (strpos(strtolower(ZLanguage::getEncoding()), 'utf') === false) {
            // accents deletion
            $permasearch = explode(',', pnConfigGetVar('permasearch'));
            $permareplace = explode(',', pnConfigGetVar('permareplace'));
            $var = str_replace($var, $permasearch, $permareplace);
            // repeated separator
            $var = str_replace($permalinksseparator . $permalinksseparator . $permalinksseparator, $permalinksseparator, $var);
            // final clean
            $var = preg_replace("/[^a-z0-9_{$permalinksseparator}]/i", '', $var);
            $var = trim($var, $permalinksseparator);
        } else {
            $res = ini_get('mbstring.func_overload');
            if ($res < 4) {
                // any mb charsets and permalinks won't work
                // add: PHP_VALUE mbstring.func_overload 6
                // to your .htaccess or php.ini file
                // sure, a hack - needs to be replaced with a more generic check
                if (pnConfigGetVar('shorturls') && ZLanguage::getLanguageCode() == 'ja') {
                    $msg = __("Error! Place 'PHP_VALUE mbstring.func_overload 4' in your server's '.htaccess' server configuration file or 'php.ini' PHP configuration file. Short URLs will not work unless you do so.");
                    LogUtil::registerError($msg);
                }
            }
            $var = preg_replace("/[[:space:]]/", $permalinksseparator, $var);
        }
        return $var;
    }

    /**
     * Censor variable contents. This method is recursive array safe.
     *
     * @param var        The variable to censor
     *
     * @return The censored variable
     */
    public static function censor($var)
    {
        static $doCensor;
        if (!isset($doCensor)) {
            $doCensor = pnModAvailable('MultiHook');
        }

        if (!$doCensor) {
            return $var;
        }

        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::censor($v);
            }
        } else {
            $var = pnModAPIFunc('MultiHook', 'user', 'censor', array('word' => $var)); // preg_replace($search, $replace, $var);
        }

        return $var;
    }

    /**
     * Perform SHA1 or SHA256 hashing on a string using native
     * PHP functions if available and if not uses own classes.
     *
     * @author Drak
     * @deprecated
     * @see hash()
     * @param $string string to be hashed
     * @param $type string element of hash_algos() (default=sha1)
     * @return string hex hash
     */
    public static function hash($string, $type = 'sha1')
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('DataUtil::hash()', 'hash()')), 'STRICT');
        return hash(strtolower($type), $string);
    }

    /**
     * This method converts the several possible return values from
     * allegedly "boolean" ini settings to proper booleans
     * Properly converted input values are: 'off', 'on', 'false', 'true', '0', '1'
     * If the ini_value doesn't match any of those, the value is returned as-is.
     *
     * @author Ed Finkler
     * @param string $ini_key   the ini_key you need the value of
     * @return boolean|mixed
     */
    public static function getBooleanIniValue($ini_key)
    {
        $ini_val = ini_get($ini_key);
        switch (strtolower($ini_val)) {
            case 'off':
                return false;
                break;
            case 'on':
                return true;
                break;
            case 'false':
                return false;
                break;
            case 'true':
                return true;
                break;
            case '0':
                return false;
                break;
            case '1':
                return true;
                break;
            default:
                return $ini_val;
        }
    }

    /**
     * check for serialization
     *
     * @param string $string
     * @param checkmb true or false
     * @return bool
     */
    public static function is_serialized($string, $checkmb = true)
    {
        if ($string == 'b:0;') {
            return true;
        }

        if ($checkmb) {
            return (self::mb_unserialize($string) === false ? false : true);
        } else {
            return (@unserialize($string) === false ? false : true);
        }
    }

    /**
     * Will unserialise serialised data that was previously encoded as iso and converted to utf8
     * This generally not required.
     *
     * @param $string serialised data
     * @return mixed
     */
    public static function mb_unserialize($string)
    {
        // we use a callback here to avoid problems with certain characters (single quotes and dollarsign) - drak
        return @unserialize(preg_replace_callback('#s:(\d+):"(.*?)";#s', create_function('$m', 'return self::_mb_unserialize_callback($m);'), $string));
    }

    /**
     * private callback function for mb_unserialize()
     * Note this is still a private method although we have to use public visibility
     *
     * @access private
     * @param string $match
     */
    public static function _mb_unserialize_callback($match)
    {
        $length = strlen($match[2]);
        return "s:$length:\"$match[2]\";";
    }

    /**
     * convertToUTF8()
     * converts a string or an array (recursivly) to utf-8
     *
     * @param input - string or array to convert to utf-8
     * @return converted string or array
     * @author Frank Schummertz
     *
     */
    public static function convertToUTF8($input = '')
    {
        if (is_array($input)) {
            $return = array();
            foreach ($input as $key => $value) {
                $return[$key] = self::convertToUTF8($value);
            }
            return $return;
        } elseif (is_string($input)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($input, 'UTF-8', strtoupper(ZLanguage::getEncoding()));
            } else {
                return utf8_encode($input);
            }
        } else {
            return $input;
        }
    }

    /**
     * convertFromUTF8()
     * converts a string from utf-8
     *
     * @param input - string or array to convert from utf-8
     * @return converted string
     * @author Frank Schummertz
     *
     */
    public static function convertFromUTF8($input = '')
    {
        if (is_array($input)) {
            $return = array();
            foreach ($input as $key => $value) {
                $return[$key] = self::convertFromUTF8($value);
            }
            return $return;
        } elseif (is_string($input)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($input, strtoupper(ZLanguage::getEncoding()), 'UTF-8');
            } else {
                return utf8_decode($input);
            }
        } else {
            return $input;
        }
    }


    /**
     * take user input and transform to a number according to locale
     * @param $number
     * @return unknown_type
     */
    public static function transformNumberInternal($number)
    {
        $i18n = ZI18n::getInstance();
        return $i18n->transformNumberInternal($number);
    }

    /**
     * transform a currency to an internal number according to locale
     * @param $number
     * @return unknown_type
     */
    public static function transformCurrencyInternal($number)
    {
        $i18n = ZI18n::getInstance();
        return $i18n->transformCurrencyInternal($number);
    }

    /**
     * format a number to currency according to locale
     *
     * @param $number
     * @return unknown_type
     */
    public static function formatCurrency($number)
    {
        $i18n = ZI18n::getInstance();
        return $i18n->transformCurrencyDisplay($number);
    }

    /**
     * format a number for display in locale
     *
     * @param $number
     * @param $decimal_points null=default locale, false=precision, int=precision
     * @return unknown_type
     */
    public static function formatNumber($number, $decimal_points=null)
    {
        $i18n = ZI18n::getInstance();
        return $i18n->transformNumberDisplay($number, $decimal_points);
    }

    /**
     * native ini file parser because PHP can't handle such a simple function cross platform
     *
     * taken from http://mach13.com/loose-and-multiline-parse_ini_file-function-in-php
     *
     * @param $iIniFile
     * @return array
     */
    public static function parseIniFile($iIniFile)
    {
        $aResult = array();
        $aMatches = array();

        $a = &$aResult;
        $s = '\s*([[:alnum:]_\- \*]+?)\s*';

        preg_match_all('#^\s*((\['.$s.'\])|(("?)'.$s.'\\5\s*=\s*("?)(.*?)\\7))\s*(;[^\n]*?)?$#ms', file_get_contents($iIniFile), $aMatches, PREG_SET_ORDER);

        foreach ($aMatches as $aMatch) {
            if (empty($aMatch[2])) {
                $a[$aMatch[6]] = $aMatch[8];
            } else {
                $a = &$aResult[$aMatch[3]];
            }
       }
       return $aResult;
    }
}

