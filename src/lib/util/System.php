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
     * Internals cache.
     *
     * @var array
     */
    protected static $cache = array();

    /**
     * Flush this static class' cache.
     *
     * @return void
     */
    public static function flushCache()
    {
        self::$cache = array();
    }

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
            $mod_var = ModUtil::getVar(ModUtil::CONFIG_MODULE, $name, $default);
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
        if (empty($name) || $name == 'system' || $name == 'prefix' || in_array($name, ServiceUtil::getManager()->getArgument('protected.systemvars'))) {
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
            if (!preg_match('/^[\p{L}\p{N}_\.\-]+$/uD', $var)) {
                return false;
            } else {
                $lowerUname = mb_strtolower($var);
                if ($lowerUname != $var) {
                    return false;
                }
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
        if (!array_key_exists('baseuri.path', self::$cache)) {
            self::$cache['baseuri.path'] = null;
        }

        if (!isset(self::$cache['baseuri.path'])) {
            $script_name = self::serverGetVar('SCRIPT_NAME');
            self::$cache['baseuri.path'] = substr($script_name, 0, strrpos($script_name, '/'));
        }

        $serviceManager = ServiceUtil::getManager();
        if ($serviceManager['multisites.enabled'] == 1) {
            self::$cache['baseuri.path'] = $serviceManager['multisites.sitedns'];
        }

        return self::$cache['baseuri.path'];
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
        $langRequired = ZLanguage::isRequiredLangParam();
        $expectEntrypoint = !self::getVar('shorturlsstripentrypoint');
        $entryPoint = self::getVar('entrypoint');

        if ($shorturls) {
            $result = self::getBaseUrl();
            if ($expectEntrypoint) {
                $result .= "$entryPoint";
            }
            if ($langRequired) {
                $result .= (preg_match('#/$#', $result) ? '' : '/') . ZLanguage::getLanguageCode();
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

        $mailer = ServiceUtil::getManager()->getService('mailer.simple');
        $altBodyContentType = ($html && $altbody) ? 'text/html' : 'plain/text';
        $failedRecipients = array();
        if ($headers) {
            $lines = explode("\n", $headers);
            $headers = array();
            foreach ($lines as $line) {
                $pairs = explode(':', $line);
                $headers[$pairs[0]] = $pairs[1];
            }
        } else {
            $headers = array(); // change to empty array
        }

        return $mailer->send((array)$from, (array)$to, $subject, $body, 'text/plain', null, null, (array)$from, $altbody, $headers, $altBodyContentType, $failedRecipients);
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
        if ((self::getVar('shorturls') && (empty($module) && empty($type) && empty($func)))) {
            // user language is not set at this stage
            $lang = System::getVar('language_i18n', '');
            $customentrypoint = self::getVar('entrypoint');
            $expectEntrypoint = !self::getVar('shorturlsstripentrypoint');
            $root = empty($customentrypoint) ? 'index.php' : $customentrypoint;

            // check if we hit baseurl, e.g. domain.com/ and if we require the language URL
            // then we should redirect to the language URL.
            if (ZLanguage::isRequiredLangParam() && self::getCurrentUrl() == self::getBaseUrl()) {
                $uri = $expectEntrypoint ? "$root/$lang" : "$lang";
                self::redirect(self::getBaseUrl() . $uri);
                self::shutDown();
            }

            // check if entry point is part of the URL expectation.  If so throw error if it's not present
            // since this URL is technically invalid.
            if ($expectEntrypoint && strpos(self::getCurrentUrl(), self::getBaseUrl() . $root) !== 0) {
                self::redirect(self::getBaseUri() . '/' . $root . str_replace(self::getBaseUri(), '', self::getCurrentUrl()));
                self::shutDown();
            }

            if (!$expectEntrypoint && self::getCurrentUrl() == self::getBaseUrl() . $root) {
                self::redirect(self::getHomepageUrl());
                self::shutDown();
            }

            if (!$expectEntrypoint && strpos(self::getCurrentUrl(), self::getBaseUrl() . $root) === 0) {
                $protocol = System::serverGetVar('SERVER_PROTOCOL');
                self::redirect(self::getBaseUri() . str_replace(self::getCurrentUrl(), self::getBaseUri() . '/' . $root));
                self::shutDown();
            }

            // get base path to work out our current url
            $parsedURL = parse_url(self::getCurrentUri());

            // strip any unwanted content from the provided URL
            $tobestripped = array(self::getBaseUri(), "$root");
            $path = str_replace($tobestripped, '', $parsedURL['path']);
            $path = trim($path, '/');

            // split the path into a set of argument strings
            $args = explode('/', rtrim($path, '/'));

            // ensure that each argument is properly decoded
            foreach ($args as $k => $v) {
                $args[$k] = urldecode($v);
            }

            $modinfo = null;
            $frontController = $expectEntrypoint ? "$root/" : '';

            // if no arguments present
            if (!$args[0] && !isset($_GET['lang']) && !isset($_GET['theme'])) {
                // we are in the homepage, checks if language code is forced
                if (ZLanguage::getLangUrlRule() && $lang) {
                    // and redirect then
                    System::redirect(self::getCurrentUrl()."/$lang");
                    System::shutDown();
                }
            } else {
                // check the existing shortURL parameters
                // validation of the first parameter as language code
                if (ZLanguage::isLangParam($args[0]) && in_array($args[0], ZLanguage::getInstalledLanguages())) {
                    // checks if the language is not enforced and this url is passing the default lang
                    if (!ZLanguage::getLangUrlRule() && $lang == $args[0]) {
                        // redirects the passed arguments without the default site language
                        array_shift($args);
                        foreach ($args as $k => $v) {
                            $args[$k] = urlencode($v);
                        }
                        System::redirect(self::getBaseUrl().$frontController.($args ? implode('/', $args) : ''));
                        System::shutDown();
                    }
                    self::queryStringSetVar('lang', $args[0]);
                    array_shift($args);

                } elseif (ZLanguage::getLangUrlRule()) {
                    // if the lang is forced, redirects the passed arguments plus the lang
                    foreach ($args as $k => $v) {
                        $args[$k] = urlencode($v);
                    }
                    $langTheme = isset($_GET['theme']) ? "$lang/$_GET[theme]" : $lang;
                    System::redirect(self::getBaseUrl().$frontController.$langTheme.'/'.implode('/', $args));
                    System::shutDown();
                }

                // check if there are remaining arguments
                if ($args) {
                    // try the first argument as a module
                    $modinfo = ModUtil::getInfoFromName($args[0]);
                    if ($modinfo) {
                        array_shift($args);
                    }
                }

                // if that fails maybe it's a theme
                if ($args && !$modinfo) {
                    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args[0]));

                    if ($themeinfo) {
                        self::queryStringSetVar('theme', $themeinfo['name']);
                        // now shift the vars and continue as before
                        array_shift($args);
                        if ($args) {
                            $modinfo = ModUtil::getInfoFromName($args[0]);
                            if ($modinfo) {
                                array_shift($args);
                            }
                        }
                    }
                }

                // if there are parameters (not homepage)
                // try to see if there's a default shortURLs module
                if ($args && !$modinfo) {
                    // add the default module handler into the code
                    $modinfo = ModUtil::getInfoFromName(self::getVar('shorturlsdefaultmodule'));
                }
            }

            // check if there is a module and a custom url handler for it
            // if not decode the url using the default handler
            if ($modinfo && $modinfo['type'] != 0) {
                // prepare the arguments to the module handler
                array_unshift($args, ''); // support for 1.2- empty parameter due the initial explode
                array_unshift($args, $modinfo['url']);
                // set the REQUEST parameters
                self::queryStringSetVar('module', $modinfo['name']);
                // the user.function name can be the second argument string, set a default
                // later the custom module handler (if exists) must setup a new one if needed
                self::queryStringSetVar('type', 'user');
                if (isset($args[2])) {
                    self::queryStringSetVar('func', $args[2]);
                } else {
                    self::queryStringSetVar('func', 'main');
                }
                if (!ModUtil::apiFunc($modinfo['name'], 'user', 'decodeurl', array('vars' => $args))) {
                    // any remaining arguments are specific to the module
                    $argscount = count($args);
                    for ($i = 3; $i < $argscount; $i = $i + 2) {
                        if (isset($args[$i]) && isset($args[$i + 1])) {
                            self::queryStringSetVar($args[$i], urldecode($args[$i + 1]));
                        }
                    }
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
            // possibly an array entry in the form a[0] or a[0][1] or a[0][1][2]
            parse_str($match[0], $data);

            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $kk => $vv) {
                        if (is_array($vv)) {
                            foreach ($vv as $kkk => $vvv) {
                                if (is_array($vvv)) {
                                    foreach ($vvv as $kkkk => $vvvv) {
                                        $_REQUEST[$k][$kk][$kkk][$kkkk] = $_GET[$k][$kk][$kkk][$kkkk] = $value;
                                    }
                                } else {
                                    $_REQUEST[$k][$kk][$kkk] = $_GET[$k][$kk][$kkk] = $value;
                                }
                            }
                        } else {
                            $_REQUEST[$k][$kk] = $_GET[$k][$kk] = $value;
                        }
                    }
                } else {
                    $_REQUEST[$k] = $_GET[$k] = $value;
                }
            }
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
     * Installer running check.
     *
     * @return boolean
     */
    public static function isInstalling()
    {
        return (bool)defined('_ZINSTALLVER');
    }

    /**
     * Check if upgrader is running.
     *
     * @return boolean True if upgrade.php is running, otherwise false.
     */
    public static function isUpgrading()
    {
        return array_key_exists('_ZikulaUpgrader', $GLOBALS);
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
     * @param string $templateFile File name of the system error template.
     *
     * @return string Template file path.
     */
    public static function getSystemErrorTemplate($templateFile)
    {
        $templatePath = "system/Theme/templates/system/$templateFile";
        $override = Zikula_View::getTemplateOverride($templatePath);
        if ($override !== false) {
            return $override;
        } elseif (self::isLegacyMode() && file_exists("config/templates/$templateFile")) {
            return "config/templates/$templateFile";
        } else {
            return $templatePath;
        }
    }
}