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
 * DataUtil is the class used to manage datas and variables.
 * @deprecated
 */
class DataUtil
{
    /**
     * Clean a variable, remove slashes. This method is recursive array safe.
     *
     * @param string $var The variable to clean
     *
     * @return string formatted variable
     */
    public static function cleanVar($var)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony request instead.', E_USER_DEPRECATED);

        return $var;
    }

    /**
     * Decode a character a previously encoded character.
     *
     * @param string $value The value we wish to encode
     *
     * @return string decoded value
     */
    public static function decode($value)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        return base64_decode($value);
    }

    /**
     * Take a name-value-pair string and convert it to an associative array, optionally urldecoding the response.
     *
     * @param string  $nvpstr    Name-value-pair String
     * @param string  $separator Separator used in the NVP string
     * @param boolean $urldecode Whether to urldecode the NVP fields
     *
     * @return array Assoc is associative array
     */
    public static function decodeNVP($nvpstr, $separator = '&', $urldecode = true)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        $assoc = [];
        $items = explode($separator, $nvpstr);
        foreach ($items as $item) {
            $fields = explode('=', $item);
            $key = $urldecode ? urldecode($fields[0]) : $fields[0];
            $value = $urldecode ? urldecode($fields[1]) : $fields[1];
            $assoc[$key] = $value;
        }

        return $assoc;
    }

    /**
     * Decrypt the given value using the mcrypt library function.
     *
     * @param string  $value   The value we wish to decrypt
     * @param string  $key     The encryption key to use (optional) (default=null)
     * @param string  $alg     The encryption algorithm to use (only used with mcrypt functions) (optional) (default=null, signifies MCRYPT_RIJNDAEL_128)
     * @param boolean $encoded Whether or not the value is base64 encoded (optional) (default=true)
     *
     * @throws RuntimeException
     * @return string The decrypted value
     */
    public static function decrypt($value, $key = null, $alg = null, $encoded = true)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        $res = false;
        $key = ($key ? $key : 'ZikulaEncryptionKey');
        $val = ($encoded ? self::decode($value) : $value);
        if (function_exists('mcrypt_create_iv') && function_exists('mcrypt_decrypt')) {
            $alg = ($alg ? $alg : MCRYPT_RIJNDAEL_128);
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($alg, MCRYPT_MODE_ECB), crc32($key));
            $res = mcrypt_decrypt($alg, $key, $val, MCRYPT_MODE_CBC);
        } else {
            throw new RuntimeException('PHP MCrypt extension is not installed');
        }

        return $res;
    }

    /**
     * Encode a character sting such that it's 8-bit clean. It maps to base64_encode().
     *
     * @param string $value The value we wish to encode
     *
     * @return string The encoded value
     */
    public static function encode($value)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        return base64_encode($value);
    }

    /**
     * Take a key and value and encode them into an NVP-string entity.
     *
     * @param string  $key          The key to encode
     * @param string  $value        The value to encode
     * @param string  $separator    The Separator to use in the NVP string
     * @param boolean $includeEmpty Whether to also include empty values
     *
     * @return string String-encoded NVP or an empty string
     */
    public static function encodeNVP($key, $value, $separator = '&', $includeEmpty = true)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        if (!$key) {
            return LogUtil::registerError('Invalid NVP key received');
        }
        if ($includeEmpty || ($value != null && strlen($value) > 1)) {
            return "&" . urlencode($key) . "=" . urlencode($value);
        }

        return '';
    }

    /**
     * Take an array and encode it as a NVP string.
     *
     * @param string  $nvps         The array of name-value paris
     * @param string  $separator    The Separator to use in the NVP string
     * @param boolean $includeEmpty Whether to also include empty values
     *
     * @return string String-encoded NVP or an empty string
     */
    public static function encodeNVPArray($nvps, $separator = '&', $includeEmpty = true)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        if (!is_array($nvps)) {
            return LogUtil::registerError('NVPS array is not an array');
        }
        $str = '';
        foreach ($nvps as $k => $v) {
            $str .= self::encodeNVP($k, $v, $separator, $includeEmpty);
        }

        return $str;
    }

    /**
     * Encrypt the given value using the mcrypt library function.
     *
     * @param string  $value   The value we wish to decrypt
     * @param string  $key     The encryption key to use (optional) (default=null)
     * @param string  $alg     The encryption algorithm to use (only used with mcrypt functions) (optional) (default=null, signifies MCRYPT_RIJNDAEL_128)
     * @param boolean $encoded Whether or not the value is base64 encoded (optional) (default=true)
     *
     * @throws RuntimeException
     * @return string The encrypted value
     */
    public static function encrypt($value, $key = null, $alg = null, $encoded = true)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        $res = false;
        $key = ($key ? $key : 'ZikulaEncryptionKey');
        if (function_exists('mcrypt_create_iv') && function_exists('mcrypt_decrypt')) {
            $alg = ($alg ? $alg : MCRYPT_RIJNDAEL_128);
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($alg, MCRYPT_MODE_ECB), crc32($key));
            $res = mcrypt_encrypt($alg, $key, $value, MCRYPT_MODE_CBC);
        } else {
            throw new \RuntimeException('PHP MCrypt extension is not installed');
        }

        return $encoded && $res ? self::encode($res) : $res;
    }

    /**
     * Format a variable for display. This method is recursive array safe.
     *
     * @param string $var The variable to format
     *
     * @return string The formatted variable
     */
    public static function formatForDisplay($var)
    {
        @trigger_error('DataUtil is deprecated, please use Twig escaping instead.', E_USER_DEPRECATED);

        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::formatForDisplay($v);
            }
        } else {
            $var = htmlspecialchars((string) $var);
            // This search and replace finds the text 'x@y' and replaces
            // it with HTML entities, this provides protection against
            // email harvesters
            $var = preg_replace_callback(
                '/(.)@(.)/s',
                function ($m) {
                    return "&#".sprintf("%03d", ord($m[1])).";&#064;&#" .sprintf("%03d", ord($m[2])) . ";";
                },
                $var);
        }

        return $var;
    }

    /**
     * Format a variable for HTML display. This method is recursive array safe.
     *
     * @param string $var The variable to format
     *
     * @return string The formatted variable
     */
    public static function formatForDisplayHTML($var)
    {
        @trigger_error('DataUtil is deprecated, please use Twig safeHtml filter instead.', E_USER_DEPRECATED);

        static $allowedtags = null;
        static $outputfilter;
        static $event;
        if (!$event) {
            $event = new \Zikula\Core\Event\GenericEvent();
        }
        if (!isset($allowedtags)) {
            $allowedHTML = [];
            $allowableHTML = System::getVar('AllowableHTML');
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
                                $allowedHTML[] = "/?\s*$k" . "(\s+[\w\-:]+\s*=\s*(\"[^\"]*\"|'[^']*'))*" . '\s*/?';
                                break;
                        }
                    }
                }
            }
            if (count($allowedHTML) > 0) {
                $allowedtags = '~<\s*(' . implode('|', $allowedHTML) . ')\s*>~is';
            } else {
                $allowedtags = '';
            }
        }
        if (!isset($outputfilter)) {
            if (ModUtil::available('ZikulaSecurityCenterModule') && ServiceUtil::getManager()->getParameter('installed')) {
                $outputfilter = System::getVar('outputfilter', 0);
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
                $event->setData($var)->setArg('filter', $outputfilter);
                $var = EventUtil::dispatch('system.outputfilter', $event)->getData();
            }
            // Preparse var to mark the HTML that we want
            if (!empty($allowedtags)) {
                $var = preg_replace($allowedtags, "\022\\1\024", $var);
            }
            // Fix html entities
            $var = htmlspecialchars($var);
            // Fix the HTML that we want
            $var = preg_replace_callback('#\022([^\024]*)\024#', create_function('$m', 'return DataUtil::formatForDisplayHTML_callback($m);'), $var);
            // Fix entities if required
            if (System::getVar('htmlentities')) {
                $var = preg_replace('/&amp;([a-z#0-9]+);/i', "&\\1;", $var);
            }
        }

        return $var;
    }

    /**
     * Function formatForDisplayHTML callback.
     *
     * @param array $m String to format
     *
     *
     * @return mixed The formatted string, or null on empty
     */
    public static function formatForDisplayHTML_callback($m)
    {
        @trigger_error('DataUtil is deprecated, please use Twig safeHtml filter instead.', E_USER_DEPRECATED);

        if (!$m) {
            return;
        }

        //return '<' . strtr($m[1], ['&gt;' => '>', '&lt;' => '<', '&quot;' => '"', '&amp;' => '&']) . '>';
        return '<' . strtr($m[1], ['&gt;' => '>', '&lt;' => '<', '&quot;' => '"']) . '>';
    }

    /**
     * Format a variable for DB-storage. This method is recursive array safe.
     *
     * @param string $var The variable to format
     *
     * @deprecated since 1.4.0
     *
     * This API is insanely unsafe. Always prepare and bind variables in SQL statements
     *
     * @return string The formatted variable
     */
    public static function formatForStore($var)
    {
        @trigger_error('DataUtil is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::formatForStore($v);
            }
        } else {
            $dbDriverName = strtolower(Doctrine_Manager::getInstance()->getCurrentConnection()->getDriverName());
            if ($dbDriverName == 'mssql' || $dbDriverName == 'oracle' || $dbDriverName == 'derby' || $dbDriverName == 'splice' || $dbDriverName == 'jdbcbridge') {
                $var = str_replace("'", "''", $var);
            } else {
                $var = addslashes($var);
            }
        }

        return $var;
    }

    /**
     * Format a variable for operating-system usage. This method is recursive array safe.
     *
     * @param string  $var      The variable to format
     * @param boolean $absolute Allow absolute paths (default=false) (optional)
     *
     * @return string The formatted variable
     */
    public static function formatForOS($var, $absolute = true)
    {
        @trigger_error('DataUtil is deprecated.', E_USER_DEPRECATED);

        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = self::formatForOS($v);
            }

            return $var;
        }

        static $cached;
        if (null === $cached) {
            $cached = [0, 1];
        }
        if (isset($cached[(int)$absolute][$var])) {
            return $cached[(int)$absolute][$var];
        }
        $orgVar = $var;
        $clean_array = [];
        //Check if it is a windows absolute path beginning with "c:" or similar
        $windowsAbsolutePath = preg_match("#^[A-Za-z]:#", $var);
        //Check if it is a linux absolute path beginning "/"
        $linuxAbsolutePath = (substr($var, 0, 1) == '/') ? true : false;
        //if we're supporting absolute paths and the first charater is a slash and , then
        //an absolute path is passed
        $absolutepathused = ($absolute && ($linuxAbsolutePath || $windowsAbsolutePath));
        // Split the path at possible path delimiters.
        // Setting PREG_SPLIT_NOEMPTY eliminates double delimiters on the fly.
        $dirty_array = preg_split('#[/\\\\]#', $var, -1, PREG_SPLIT_NO_EMPTY);
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
        // Build the path
        // Rather than use DIRECTORY_SEPARATOR, normalise the $var because we cannot be sure what we got
        // and since we cannot use realpath() because this will turn paths into absolute - for legacy reasons
        // recipient's of the call may not be expecting absolute values (drak).
        $var = str_replace('\\', '/', $var);
        $var = implode('/', $clean_array);
        // If an absolute linux path was passed to the function, we need to make it absolute again
        // An absolute windows path is still absolute.
        if ($absolutepathused && !$windowsAbsolutePath) {
            $var = '/' . $var;
        }
        // Prepare var
        // needed for magic_quotes_runtime = 0
        $var = addslashes($var);
        $cached[$orgVar] = $var;

        return $var;
    }

    /**
     * Format a variable for URL usage.
     *
     * @param string $var The variable to format
     *
     * @return string The formatted variable for URL usage
     */
    public static function formatForURL($var)
    {
        @trigger_error('DataUtil is deprecated, please use Doctrine slug extension instead.', E_USER_DEPRECATED);

        return self::formatPermalink($var);
    }

    /**
     * Format a variable for permalink usage.
     *
     * @param string $var The variable to format
     *
     * @return string The formatted variable for permalink usage
     */
    public static function formatPermalink($var)
    {
        @trigger_error('DataUtil is deprecated, please use Doctrine slug extension instead.', E_USER_DEPRECATED);

        // replace all chars $permasearch with the one in $permareplace
        $permaSearch = explode(',', System::getVar('permasearch'));
        $permaReplace = explode(',', System::getVar('permareplace'));
        foreach ($permaSearch as $key => $value) {
            $var = mb_ereg_replace($value, $permaReplace[$key], $var);
        }

        $var = preg_replace("#(\s*\/\s*|\s*\+\s*|\s+)#", '-', strtolower($var));

        // final clean
        $permalinksseparator = System::getVar('shorturlsseparator');
        $var = mb_ereg_replace("[^a-z0-9_{$permalinksseparator}]", '', $var, "imsr");
        $var = preg_replace('/' . $permalinksseparator . '+/', $permalinksseparator, $var); // remove replicated separator
        $var = trim($var, $permalinksseparator);

        return $var;
    }

    /**
     * Transliterate a variable.
     *
     * @param string $var The variable to format
     *
     * @return string The formatted variable
     */
    public static function formatTransliterate($var)
    {
        @trigger_error('DataUtil is deprecated, please use Doctrine slug extension instead.', E_USER_DEPRECATED);

        $strIsUpper = (strcmp($var, mb_strtoupper($var)) == 0);
        // replace all chars $permasearch with the one in $permareplace
        $permaSearch = explode(',', System::getVar('permasearch'));
        $permaReplace = explode(',', System::getVar('permareplace'));
        foreach ($permaSearch as $key => $value) {
            $var = mb_ereg_replace($value, $permaReplace[$key], $var);
        }
        if ($strIsUpper) {
            $var = mb_strtoupper($var);
        }

        return $var;
    }

    /**
     * Hash function.
     *
     * Perform SHA1 or SHA256 hashing on a string using native PHP functions if available and if not uses own classes.
     *
     * @param string $string String to be hashed
     * @param string $type   String element of hash_algos() (default=sha1)
     *
     * @deprecated
     * @see    hash()
     * @return string hex hash
     */
    public static function hash($string, $type = 'sha1')
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', ['DataUtil::hash()', 'hash()']), E_USER_DEPRECATED);

        return hash(strtolower($type), $string);
    }

    /**
     * Get boolean ini value.
     *
     * This method converts the several possible return values from
     * allegedly "boolean" ini settings to proper booleans
     * Properly converted input values are: 'off', 'on', 'false', 'true', '0', '1'
     * If the ini_value doesn't match any of those, the value is returned as-is.
     *
     * @param string $ini_key The ini_key you need the value of
     *
     * @return boolean|mixed
     */
    public static function getBooleanIniValue($ini_key)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

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
     * Check for serialization.
     *
     * @param string  $string  String to check
     * @param boolean $checkmb True or false
     *
     * @return boolean
     */
    public static function is_serialized($string, $checkmb = true)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        if ($string == 'b:0;') {
            return true;
        }

        if ($checkmb) {
            return self::mb_unserialize($string) === false ? false : true;
        }

        return @unserialize($string) === false ? false : true;
    }

    /**
     * Unserialize function.
     *
     * Will unserialise serialised data that was previously encoded as iso and converted to utf8
     * This generally not required.
     *
     * @param string $string Serialised data
     *
     * @return mixed
     */
    public static function mb_unserialize($string)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        // we use a callback here to avoid problems with certain characters (single quotes and dollarsign) - drak
        return @unserialize(preg_replace_callback('#s:(\d+):"(.*?)";#s', create_function('$m', 'return DataUtil::_mb_unserialize_callback($m);'), $string));
    }

    /**
     * Private callback function for mb_unserialize().
     *
     * Note this is still a private method although we have to use public visibility.
     *
     * @param string $match String to use
     *
     * @return string
     */
    public static function _mb_unserialize_callback($match)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        $length = strlen($match[2]);

        return "s:$length:\"$match[2]\";";
    }

    /**
     * Convert to UTF8 function.
     *
     * Converts a string or an array (recursivly) to utf-8.
     *
     * @param mixed $input String or array to convert to utf-8
     *
     * @return mixed Converted string or array
     */
    public static function convertToUTF8($input = '')
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        if (is_array($input)) {
            $return = [];
            foreach ($input as $key => $value) {
                $return[$key] = self::convertToUTF8($value);
            }

            return $return;
        }

        if (is_string($input)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($input, 'UTF-8', strtoupper(ZLanguage::getEncoding()));
            }

            return utf8_encode($input);
        }

        return $input;
    }

    /**
     * Convert a string from utf-8.
     *
     * @param mixed $input String or array to convert from utf-8
     *
     * @return mixed Converted string
     */
    public static function convertFromUTF8($input = '')
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        if (is_array($input)) {
            $return = [];
            foreach ($input as $key => $value) {
                $return[$key] = self::convertFromUTF8($value);
            }

            return $return;
        }

        if (is_string($input)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($input, strtoupper(ZLanguage::getEncoding()), 'UTF-8');
            }

            return utf8_decode($input);
        }

        return $input;
    }

    /**
     * Take user input and transform to a number according to locale.
     *
     * @param mixed $number Number to transform
     *
     * @return mixed
     */
    public static function transformNumberInternal($number)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        $i18n = ZI18n::getInstance();

        return $i18n->transformNumberInternal($number);
    }

    /**
     * Transform a currency to an internal number according to locale.
     *
     * @param mixed $number Number to transform
     *
     * @return mixed
     */
    public static function transformCurrencyInternal($number)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        $i18n = ZI18n::getInstance();

        return $i18n->transformCurrencyInternal($number);
    }

    /**
     * Format a number to currency according to locale.
     *
     * @param mixed $number Number to format
     *
     * @return unknown_type
     */
    public static function formatCurrency($number)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or Twig extensions instead.', E_USER_DEPRECATED);

        $i18n = ZI18n::getInstance();

        return $i18n->transformCurrencyDisplay($number);
    }

    /**
     * Format a number for display in locale.
     *
     * @param mixed $number         Number to format
     * @param mixed $decimal_points Desc : null=default locale, false=precision, int=precision
     *
     * @return mixed
     */
    public static function formatNumber($number, $decimal_points = null)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or Twig extensions instead.', E_USER_DEPRECATED);

        $i18n = ZI18n::getInstance();

        return $i18n->transformNumberDisplay($number, $decimal_points);
    }

    /**
     * Parse ini file.
     *
     * @param string  $iniFile          The file name of the ini file to parse
     * @param boolean $process_sections If true, a multidimensional array is returned with section names included
     *
     * @deprecated since 1.3.0,  see {@link parse_ini_file()}
     *
     * @return array|boolean An associative array of ini file settings, or false on failure
     */
    public static function parseIniFile($iniFile, $process_sections = true)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', ['DataUtil::parseIniFile()', 'parse_ini_file()']), E_USER_DEPRECATED);

        return parse_ini_file($iniFile, $process_sections);
    }

    /**
     * Encode json data to url safe format.
     *
     * @param mixed   $data Data to encode
     * @param boolean $json Should data be also encode to json
     *
     * @return string Encoded data
     */
    public static function urlsafeJsonEncode($data, $json = true)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        if ($json) {
            $data = json_encode($data);
        }

        return urlencode($data);
    }

    /**
     * Decode json data from url safe format.
     *
     * @param string  $data Data to encode
     * @param boolean $json Should data be also encode to json
     *
     * @return mixed Decoded data
     */
    public static function urlsafeJsonDecode($data, $json = true)
    {
        @trigger_error('DataUtil is deprecated, please use Symfony or native PHP functions instead.', E_USER_DEPRECATED);

        $data = urldecode($data);
        if ($json) {
            $data = json_decode($data, true);
        }

        return $data;
    }
}
