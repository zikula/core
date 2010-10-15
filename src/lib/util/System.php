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

/**
 * System class.
 *
 * Core class with the base methods.
 */
class System
{
    /**
     * The core Zikula version number.
     */
    const VERSION_NUM = '1.3.0-dev';

    /**
     * The version ID.
     */
    const VERSION_ID = 'Zikula';

    /**
     * The version sub-ID.
     */
    const VERSION_SUB = 'vai';

    const STAGES_NONE = 0;
    const STAGES_PRE = 1;
    const STAGES_POST = 2;
    const STAGES_CONFIG = 4;
    const STAGES_DB = 8;
    const STAGES_TABLES = 16;
    const STAGES_SESSIONS = 32;
    const STAGES_LANGS = 64;
    const STAGES_MODS = 128;
    const STAGES_DECODEURLS = 1024;
    const STAGES_THEME = 2048;
    const STAGES_ALL = 4095;
    const STAGES_AJAX = 4096;
    // needs to be set explicitly, STAGES_ALL | STAGES_AJAX

    /**
     * Get a configuration variable.
     *
     * @param string $name    The name of the variable.
     * @param mixed  $default The default value to return if the requested param is not set.
     *
     * @return mixed Value of the variable, or false on failure.
     */
    public static function getVar($name, $default = null)
    {
        if (!isset($name)) {
            return null;
        }

        if (array_key_exists($name, $GLOBALS['ZConfig']['System'])) {
            $mod_var = $GLOBALS['ZConfig']['System'][$name];
        } else {
            $mod_var = ModUtil::getVar(ModUtil::CONFIG_MODULE, $name);
            // cache
            $GLOBALS['ZConfig']['System'][$name] = $mod_var;
        }

        // Known Issue: the $default value will never be used because $mod_var returned from ModUtil::getVar will
        // be false if the value does not exist in the database. This function will always return false in this
        // case. Unfortunately legacy code relies on the behavior, so it cannot be fixed. See issues #1025, #2011
        // and possibly others.
        if (isset($mod_var)) {
            return $mod_var;
        }

        return $default;
    }

    /**
     * Set a configuration variable.
     *
     * @param string $name  The name of the variable.
     * @param mixed  $value The value of the variable.
     *
     * @return boolean True on success, false on failure.
     */
    public static function setVar($name, $value = '')
    {
        $name = isset($name) ? (string)$name : '';

        // The database parameter are not allowed to change
        if (empty($name) || $name == 'dbtype' || $name == 'dbhost' || $name == 'dbuname' || $name == 'dbpass' || $name == 'dbname' || $name == 'system' || $name == 'prefix' || $name == 'encoded') {
            return false;
        }

        // set the variable
        if (ModUtil::setVar(ModUtil::CONFIG_MODULE, $name, $value)) {
            // Update my vars
            $GLOBALS['ZConfig']['System'][$name] = $value;
            return true;
        }

        return false;
    }

    /**
     * Delete a configuration variable.
     *
     * @param string $name The name of the variable.
     *
     * @return mixed Value of deleted config var or false on failure.
     */
    public static function delVar($name)
    {
        if (!isset($name)) {
            return false;
        }

        // The database parameter are not allowed to be deleted
        if (empty($name) || $name == 'dbtype' || $name == 'dbhost' || $name == 'dbuname' || $name == 'dbpass' || $name == 'dbname' || $name == 'system' || $name == 'prefix' || $name == 'encoded') {
            return false;
        }

        // Delete the variable
        ModUtil::delVar(ModUtil::CONFIG_MODULE, $name);

        // Update my vars
        $val = false;
        if (array_key_exists($name, $GLOBALS['ZConfig']['System'])) {
            $val = $GLOBALS['ZConfig']['System'][$name];
            unset($GLOBALS['ZConfig']['System'][$name]);
        }

        // success
        return $val;
    }

    /**
     * Strip slashes.
     *
     * Stripslashes on multidimensional arrays.
     * Used in conjunction with FormUtil::getPassedValue.
     *
     * @param mixed &$value Variables or arrays to be stripslashed.
     *
     * @return void
     */
    public static function stripslashes(&$value)
    {
        if (empty($value)) {
            return;
        }

        if (!is_array($value)) {
            $value = stripslashes($value);
        } else {
            array_walk($value, 'System::stripslashes');
        }
    }

    /**
     * Validate a Zikula variable.
     *
     * @param mixed  $var  The variable to validate.
     * @param string $type The type of the validation to perform (email, url etc.).
     * @param mixed  $args Optional array with validation-specific settings (deprecated).
     *
     * @return boolean True if the validation was successful, false otherwise.
     */
    public static function varValidate($var, $type, $args = 0)
    {
        if (!isset($var) || !isset($type)) {
            return false;
        }

        // typecasting (might be useless in this function)
        $var = (string)$var;
        $type = (string)$type;

        static $maxlength = array(
        'modvar' => 64,
        'func' => 512,
        'api' => 187,
        'theme' => 200,
        'uname' => 25,
        'config' => 64);

        static $minlength = array(
        'mod' => 1,
        'modvar' => 1,
        'uname' => 1,
        'config' => 1);

        // commented out some regexps until some useful and working ones are found
        static $regexp = array(// 'mod'    => '/^[^\\\/\?\*\"\'\>\<\:\|]*$/',
        // 'func'   => '/[^0-9a-zA-Z_]/',
        // 'api'    => '/[^0-9a-zA-Z_]/',
        // 'theme'  => '/^[^\\\/\?\*\"\'\>\<\:\|]*$/',
        'email' => '/^(?:[^\s\000-\037\177\(\)<>@,;:\\"\[\]]\.?)+@(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}$/Ui',
        'url' => '/^([!#\$\046-\073=\077-\132_\141-\172~]|(?:%[a-f0-9]{2}))+$/i');

        // special cases
        if ($type == 'mod' && $var == ModUtil::CONFIG_MODULE) {
            return true;
        }

        if ($type == 'config' && ($var == 'dbtype') || ($var == 'dbhost') || ($var == 'dbuname') || ($var == 'dbpass') || ($var == 'dbname') || ($var == 'system') || ($var == 'prefix') || ($var == 'encoded')) {
            // The database parameter are not allowed to change
            return false;
        }

        if ($type == 'email' || $type == 'url') {
            // CSRF protection for email and url
            $var = str_replace(array(
                            '\r',
                            '\n',
                            '%0d',
                            '%0a'), '', $var);

            if (self::getVar('idnnames')) {
                // transfer between the encoded (Punycode) notation and the decoded (8bit) notation.
                require_once 'lib/vendor/idn/idna_convert.class.php';
                $IDN = new idna_convert();
                $var = $IDN->encode(DataUtil::convertToUTF8($var));
            }
            // all characters must be 7 bit ascii
            $length = strlen($var);
            $idx = 0;
            while ($length--) {
                $c = $var[$idx++];
                if (ord($c) > 127) {
                    return false;
                }
            }
        }

        if ($type == 'url') {
            // check for url
            $url_array = @parse_url($var);
            if (!empty($url_array) && empty($url_array['scheme'])) {
                return false;
            }
        }

        if ($type == 'uname') {
            // check for invalid characters
            if (preg_match('/[^\p{L}\p{N}_\.\-]/u', $var)) {
                return false;
            }
        }

        // variable passed special checks. We now to generic checkings.
        // check for maximal length
        if (isset($maxlength[$type]) && mb_strlen($var) > $maxlength[$type]) {
            return false;
        }

        // check for minimal length
        if (isset($minlength[$type]) && mb_strlen($var) < $minlength[$type]) {
            return false;
        }

        // check for regular expression
        if (isset($regexp[$type]) && !preg_match($regexp[$type], $var)) {
            return false;
        }

        // all tests for illegal entries failed, so we assume the var is ok ;-)
        return true;
    }

    /**
     * Get base URI for Zikula.
     *
     * @return string Base URI for Zikula.
     */
    public static function getBaseUri()
    {
        static $path;

        if (!isset($path)) {
            $script_name = self::serverGetVar('SCRIPT_NAME');
            $path = substr($script_name, 0, strrpos($script_name, '/'));
        }

        $serviceManager = ServiceUtil::getManager();
        if ($serviceManager['multisites.enabled'] == 1) {
            $path = $serviceManager['multisites.sitedns'];
        }

        return $path;
    }

    /**
     * Get base URL for Zikula.
     *
     * @return string Base URL for Zikula.
     */
    public static function getBaseUrl()
    {
        $server = self::serverGetVar('HTTP_HOST');

        // IIS sets HTTPS=off
        $https = self::serverGetVar('HTTPS', 'off');
        if ($https != 'off') {
            $proto = 'https://';
        } else {
            $proto = 'http://';
        }

        $path = self::getBaseUri();

        return "$proto$server$path/";
    }

    /**
     * Get homepage URL for Zikula.
     *
     * @return string Homepage URL for Zikula.
     */
    public static function getHomepageUrl()
    {
        // check the use of friendly url setup
        $shorturls = self::getVar('shorturls', false);
        $dirBased = (self::getVar('shorturlstype') == 0 ? true : false);
        $langRequired = ZLanguage::isRequiredLangParam();

        if ($shorturls && $dirBased) {
            $result = self::getBaseUrl();
            if ($langRequired) {
                $result .= ZLanguage::getLanguageCode();
            }
        } else {
            $result = self::getVar('entrypoint', 'index.php');
            if (ZLanguage::isRequiredLangParam()) {
                $result .= '?lang=' . ZLanguage::getLanguageCode();
            }
        }

        return $result;
    }

    /**
     * Carry out a redirect.
     *
     * @param string  $redirecturl       URL to redirect to.
     * @param array   $additionalheaders Array of header strings to send with redirect.
     * @param integer $type              Number type of the redirect.
     *
     * @return boolean True if redirect successful, false otherwise.
     */
    public static function redirect($redirecturl, $additionalheaders = array(), $type = 302)
    {
        // very basic input validation against HTTP response splitting
        $redirecturl = str_replace(array(
                        '\r',
                        '\n',
                        '%0d',
                        '%0a'), '', $redirecturl);

        // check if the headers have already been sent
        if (headers_sent ()) {
            return false;
        }

        // Always close session before redirect
        session_write_close();

        // add any additional headers supplied
        if (!empty($additionalheaders)) {
            foreach ($additionalheaders as $additionalheader) {
                header($additionalheader);
            }
        }

        if (preg_match('!^(?:http|https|ftp|ftps):\/\/!', $redirecturl)) {
            // Absolute URL - simple redirect
        } elseif (substr($redirecturl, 0, 1) == '/') {
            // Root-relative links
            $redirecturl = 'http' . (self::serverGetVar('HTTPS') == 'on' ? 's' : '') . '://' . self::serverGetVar('HTTP_HOST') . $redirecturl;
        } else {
            // Relative URL
            // Removing leading slashes from redirect url
            $redirecturl = preg_replace('!^/*!', '', $redirecturl);
            // Get base URL and append it to our redirect url
            $baseurl = self::getBaseUrl();
            $redirecturl = $baseurl . $redirecturl;
        }

        header("Location: $redirecturl", true, (int)$type);

        return true;
    }

    /**
     * Check to see if this is a local referral.
     *
     * @param boolean $strict Strict checking ensures that a referer must be set as well as local.
     *
     * @return boolean True if locally referred, false if not.
     */
    public static function localReferer($strict = false)
    {
        $server = self::serverGetVar('HTTP_HOST');
        $referer = self::serverGetVar('HTTP_REFERER');

        // an empty referer returns true unless strict checking is enabled
        if (!$strict && empty($referer)) {
            return true;
        }

        // check the http referer
        if (preg_match("!^https?://$server/!", $referer)) {
            return true;
        }

        return false;
    }

    /**
     * Send an email.
     *
     * E-mail messages should now be send with a ModUtil::apiFunc call to the mailer module.
     *
     * @param string  $to      Recipient of the email.
     * @param string  $subject Title of the email.
     * @param string  $message Body of the email.
     * @param string  $headers Extra headers for the email.
     * @param integer $html    Message is html formatted.
     * @param integer $debug   If 1, echo mail content.
     * @param string  $altbody Alternative body.
     *
     * @deprecated
     *
     * @return boolean True if the email was sent, false if not.
     */
    public static function mail($to, $subject, $message = '', $headers = '', $html = 0, $debug = 0, $altbody = '')
    {
        if (empty($to) || !isset($subject)) {
            return false;
        }

        // set initial return value until we know we have a valid return
        $return = false;

        // check if the mailer module is availble and if so call the API
        if ((ModUtil::available('Mailer'))) {
            $return = ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array(
                            'toaddress' => $to,
                            'subject' => $subject,
                            'headers' => $headers,
                            'body' => $message,
                            'altbody' => $altbody,
                            'headers' => $headers,
                            'html' => $html));
        }

        return $return;
    }

    /**
     * Gets a server variable.
     *
     * Returns the value of $name from $_SERVER array.
     * Accepted values for $name are exactly the ones described by the
     * {@link http://www.php.net/manual/en/reserved.variables.html#reserved.variables.server PHP manual}.
     * If the server variable doesn't exist void is returned.
     *
     * @param string $name    The name of the variable.
     * @param mixed  $default The default value to return if the requested param is not set.
     *
     * @return mixed Value of the variable.
     */
    public static function serverGetVar($name, $default = null)
    {
        // Check the relevant superglobals
        if (!empty($name) && isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        return $default; // nothing found -> return default
    }

    /**
     * Gets the host name.
     *
     * Returns the server host name fetched from HTTP headers when possible.
     * The host name is in the canonical form (host + : + port) when the port is different than 80.
     *
     * @return string HTTP host name.
     */
    public static function getHost()
    {
        $server = self::serverGetVar('HTTP_HOST');

        if (empty($server)) {
            // HTTP_HOST is reliable only for HTTP 1.1
            $server = self::serverGetVar('SERVER_NAME');
            $port = self::serverGetVar('SERVER_PORT');
            if ($port != '80') {
                $server .= ":$port";
            }
        }

        return $server;
    }

    /**
     * Get current URI (and optionally add/replace some parameters).
     *
     * @param array $args Additional parameters to be added to/replaced in the URI (e.g. theme, ...).
     *
     * @access public
     *
     * @return string Current URI.
     */
    public static function getCurrentUri($args = array())
    {
        // get current URI
        $request = self::serverGetVar('REQUEST_URI');

        if (empty($request)) {
            $scriptname = self::serverGetVar('SCRIPT_NAME');
            $pathinfo = self::serverGetVar('PATH_INFO');
            if ($pathinfo == $scriptname) {
                $pathinfo = '';
            }
            if (!empty($scriptname)) {
                $request = $scriptname . $pathinfo;
                $querystring = self::serverGetVar('QUERY_STRING');
                if (!empty($querystring)) {
                    $request .= '?' . $querystring;
                }
            } else {
                $request = '/';
            }
        }

        // add optional parameters
        if (count($args) > 0) {
            if (strpos($request, '?') === false) {
                $request .= '?';
            } else {
                $request .= '&';
            }

            foreach ($args as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $l => $w) {
                        // TODO: replace in-line here too ?
                        if (!empty($w)) {
                            $request .= $k . "[$l]=$w&";
                        }
                    }
                } else {
                    // if this parameter is already in the query string...
                    if (preg_match("/(&|\?)($k=[^&]*)/", $request, $matches)) {
                        $find = $matches[2];
                        // ... replace it in-line if it's not empty
                        if (!empty($v)) {
                            $request = preg_replace("/(&|\?)$find/", "$1$k=$v", $request);
                            // ... or remove it otherwise
                        } elseif ($matches[1] == '?') {
                            $request = preg_replace("/\?$find(&|)/", '?', $request);
                        } else {
                            $request = preg_replace("/&$find/", '', $request);
                        }
                    } elseif (!empty($v)) {
                        $request .= "$k=$v&";
                    }
                }
            }

            $request = substr($request, 0, -1);
        }

        return $request;
    }

    /**
     * Gets the current protocol.
     *
     * Returns the HTTP protocol used by current connection, it could be 'http' or 'https'.
     *
     * @return string Current HTTP protocol.
     */
    public static function serverGetProtocol()
    {
        if (preg_match('/^http:/', self::getCurrentUri())) {
            return 'http';
        }

        $HTTPS = self::serverGetVar('HTTPS');

        // IIS seems to set HTTPS = off for some reason
        return (!empty($HTTPS) && $HTTPS != 'off') ? 'https' : 'http';
    }

    /**
     * Get current URL.
     *
     * @param array $args Additional parameters to be added to/replaced in the URL (e.g. theme, ...).
     *
     * @access public
     * @todo cfr. BaseURI() for other possible ways, or try PHP_SELF.
     *
     * @return string Current URL.
     */
    public static function getCurrentUrl($args = array())
    {
        $server = self::getHost();
        $protocol = self::serverGetProtocol();
        $baseurl = "$protocol://$server";
        $request = self::getCurrentUri($args);

        if (empty($request)) {
            $scriptname = self::serverGetVar('SCRIPT_NAME');
            $pathinfo = self::serverGetVar('PATH_INFO');
            if ($pathinfo == $scriptname) {
                $pathinfo = '';
            }
            if (!empty($scriptname)) {
                $request = $scriptname . $pathinfo;
                $querystring = self::serverGetVar('QUERY_STRING');
                if (!empty($querystring)) {
                    $request .= '?' . $querystring;
                }
            } else {
                $request = '/';
            }
        }

        return $baseurl . $request;
    }

    /**
     * Decode the path string into a set of variable/value pairs.
     *
     * This API works in conjunction with the new short urls
     * system to extract a path based variable set into the Get, Post
     * and request superglobals.
     * A sample path is /modname/function/var1:value1.
     *
     * @return void
     */
    public static function queryStringDecode()
    {
        if (self::isInstalling()) {
            return;
        }

        // get our base parameters to work out if we need to decode the url
        $module = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $func = FormUtil::getPassedValue('func', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $type = FormUtil::getPassedValue('type', null, 'GETPOST', FILTER_SANITIZE_STRING);

        // check if we need to decode the url
        if ((self::getVar('shorturls') && self::getVar('shorturlstype') == 0 && (empty($module) && empty($type) && empty($func)))) {
            // define our site entry points
            $customentrypoint = self::getVar('entrypoint');
            $root = empty($customentrypoint) ? 'index.php' : $customentrypoint;
            $tobestripped = array(
                    "/$root",
                    self::getBaseUri());

            // get base path to work out our current url
            $parsedURL = parse_url(self::getCurrentUri());

            // strip any unwanted content from the provided URL
            $path = str_replace($tobestripped, '', $parsedURL['path']);

            // split the path into a set of argument strings
            $args = explode('/', rtrim($path, '/'));

            // ensure that each argument is properly decoded
            foreach ($args as $k => $v) {
                $args[$k] = urldecode($v);
            }
            // the module is the first argument string
            if (isset($args[1]) && !empty($args[1])) {
                if (ZLanguage::isLangParam($args[1]) && in_array($args[1], ZLanguage::getInstalledLanguages())) {
                    self::queryStringSetVar('lang', $args[1]);
                    array_shift($args);
                }

                $name = isset($args[1]) ? $args[1] : false;
                // first try the first argument as a module
                $modinfo = ($name) ? ModUtil::getInfoFromName($name) : false;
                // if that fails it's a theme
                if (!$modinfo) {
                    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($name));
                    if ($themeinfo) {
                        self::queryStringSetVar('theme', $themeinfo['name']);
                        // now shift the vars and continue as before
                        array_shift($args);
                        $modinfo = isset($args[1]) ? ModUtil::getInfoFromName($args[1]) : false;
                    } else {
                        // add the default module handler into the code
                        $modinfo = ModUtil::getInfoFromName(self::getVar('shorturlsdefaultmodule'));
                        array_unshift($args, $modinfo['url']);
                    }
                }
                self::queryStringSetVar('module', $modinfo['name']);
                // the function name is the second argument string
                isset($args[2]) ? self::queryStringSetVar('func', $args[2]) : null;
                $modname = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
            }

            // check if there is a custom url handler for this module
            // if not decode the url using the default handler
            if (isset($modinfo) && $modinfo['type'] != 0 && !ModUtil::apiFunc($modname, 'user', 'decodeurl', array(
                            'vars' => $args))) {
                // any remaining arguments are specific to the module
                $argscount = count($args);
                for ($i = 3; $i < $argscount; $i = $i + 2) {
                    if (isset($args[$i]))
                        self::queryStringSetVar($args[$i], urldecode($args[$i + 1]));
                }
            }
        }
    }

    /**
     * Add a variable/value pair into the query string.
     *
     * Really the _GET superglobal.
     * This API also adds the variable to the _REQUEST superglobal for consistency.
     *
     * @param string $name  Name of the variable to set.
     * @param mixed  $value Value to set.
     *
     * @return bool True if successful, false otherwise.
     */
    public static function queryStringSetVar($name, $value)
    {
        if (!isset($name)) {
            return false;
        }

        // add the variable into the get superglobal
        $res = preg_match('/(.*)\[(.*)\]/i', $name, $match);

        if ($res != 0) {
            // possibly an array entry in the form a[0] or b[c]
            // $match[0] = a[0]
            // $match[1] = a
            // $match[2] = 0
            // this is everything we need to continue to build an array
            if (!isset($_REQUEST[$match[1]])) {
                $_REQUEST[$match[1]] = $_GET[$match[1]] = array();
            }
            $_REQUEST[$match[1]][$match[2]] = $_GET[$match[1]][$match[2]] = $value;
        } else {
            $_REQUEST[$name] = $_GET[$name] = $value;
        }

        return true;
    }

    /**
     * Shutdown.
     *
     * Gracefully shut down the framework (traps all exit and die calls),
     * Function halts execution.
     *
     * @param mixed $exit_param String or integer params to pass to the exit function.
     *
     * @return void
     */
    public static function shutDown($exit_param = '')
    {
        // we must deliberately cause the session to close down because if we
        // rely on PHP to do so after an exit is called, the framework gets shutdown
        // by PHP and no longer functions correctly.
        session_write_close();

        // do the exit
        if (empty($exit_param)) {
            exit();
        } else {
            exit($exit_param);
        }
    }

    /**
     * Perform some checks that might result in a die() upon failure.
     *
     * @return void
     */
    public static function checks()
    {
        $die = false;

        if (get_magic_quotes_runtime()) {
            echo __f('Sorry, but Zikula does not support PHP magic_quotes_runtime - please disable this feature in your php.ini file.');
            $die = true;
        }

        if (ini_get('register_globals')) {
            echo __f('Sorry, but Zikula does not support PHP register_globals = On - please disable this feature in your php.ini or .htaccess file.');
            $die = true;
        }

        // check PHP version, shouldn't be necessary, but....
        if (version_compare(PHP_VERSION, '5.2.6', '>=') == false) {
            echo __f('Error! Stop, please! PHP version 5.2.6 or a newer version is needed. The latest version of PHP 5 is what is actually recommended. Your server seems to be using version %s.', PHP_VERSION);
            $die = true;
        }

        // token_get_all needed for Smarty
        if (!function_exists('token_get_all')) {
            echo __("Error! Stop, please! The PHP function 'token_get_all()' is needed, but is not available.");
            $die = true;
        }

        // mb_string is needed too
        if (!function_exists('mb_get_info')) {
            echo __("Error! Stop, please! The 'mbstring' extension for PHP is needed, but is not available.");
            $die = true;
        }

        if (!function_exists('fsockopen')) {
            echo __("Error! The PHP function 'fsockopen()' is needed within the Zikula mailer module, but is not available.");
            $die = true;
        }

        if (self::isDevelopmentMode() || self::isInstalling()) {
            $temp = self::getVar('temp');

            $folders = array(
                    $temp,
                    "$temp/error_logs",
                    "$temp/view_compiled",
                    "$temp/view_cache",
                    "$temp/Theme_compiled",
                    "$temp/Theme_cache",
                    "$temp/Theme_Config",
                    "$temp/Theme_cache",
                    "$temp/purifierCache",
                    "$temp/idsTmp"
            );

            foreach ($folders as $folder) {
                if (!is_dir($folder)) {
                    mkdir($folder, ServiceUtil::getManager()->getArgument('system.chmod_dir'), true);
                }
                if (!is_writable($folder)) {
                    echo __f("Error! Stop, please! '%s' was not found, or else the server does not have write permission for it.", $folder) . '<br />';
                    $die = true;
                }
            }
        }
        if ($die == true) {
            exit;
        }
    }

    /**
     * Installer running check.
     *
     * @return boolean
     */
    public static function isInstalling()
    {
        return (bool)defined('_ZINSTALLVER');
    }

    /**
     * Legacy mode enabled check.
     *
     * @return boolean
     */
    public static function isLegacyMode()
    {
        if (!isset($GLOBALS['ZConfig']['System']['compat_layer'])) {
            return false;
        }

        return (bool)$GLOBALS['ZConfig']['System']['compat_layer'];
    }

    /**
     * Legacy prefilters check.
     *
     * @return boolean
     */
    public static function hasLegacyTemplates()
    {
        if (!isset($GLOBALS['ZConfig']['System']['legacy_prefilters'])) {
            return false;
        }

        return (bool)$GLOBALS['ZConfig']['System']['legacy_prefilters'];
    }

    /**
     * Development mode enabled check.
     *
     * @return boolean
     */
    public static function isDevelopmentMode()
    {
        if (!isset($GLOBALS['ZConfig']['System']['development'])) {
            return false;
        }
        return (bool)$GLOBALS['ZConfig']['System']['development'];
    }

    /**
     * Get a system error template.
     *
     * @param string $templateFile
     * @param string $templatePath
     *
     * @return string Template file path.
     */
    public static function getSystemErrorTemplate($templateFile)
    {
        $templatePath = "system/Theme/templates/system/$templateFile";
        $override = Zikula_View::getTemplateOverride($templatePath);
        if ($override !== false) {
            return $override;
        } else if (self::isLegacyMode() && file_exists("config/templates/$templateFile")) {
            return "config/templates/$templateFile";
        } else {
            return $templatePath;
        }
    }

}
