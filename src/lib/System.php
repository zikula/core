<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Yes/no integer
 */
define('ZYES', 1);
define('ZNO', 0);

/**
 * Fake module for config vars
 */
define('CONFIG_MODULE', '/PNConfig');

/*
 *  Defines for access levels
*/
define('ACCESS_INVALID', -1);
define('ACCESS_NONE', 0);
define('ACCESS_OVERVIEW', 100);
define('ACCESS_READ', 200);
define('ACCESS_COMMENT', 300);
define('ACCESS_MODERATE', 400);
define('ACCESS_EDIT', 500);
define('ACCESS_ADD', 600);
define('ACCESS_DELETE', 700);
define('ACCESS_ADMIN', 800);

ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
mb_regex_encoding('UTF-8');

include 'lib/api/debug.php';

/**
 * Functions
 */
class System
{
    const VERSION_NUM = '1.3.0-dev';
    const VERSION_ID = 'Zikula';
    const VERSION_SUB =  'cinco';

    const CORE_STAGES_NONE = 0;
    const CORE_STAGES_CONFIG = 1;
    const CORE_STAGES_ADODB = 2; // deprecated
    const CORE_STAGES_DB = 4;
    const CORE_STAGES_OBJECTLAYER = 8;
    const CORE_STAGES_TABLES = 16;
    const CORE_STAGES_SESSIONS = 32;
    const CORE_STAGES_LANGS = 64;
    const CORE_STAGES_MODS = 128;
    const CORE_STAGES_TOOLS = 256; // deprecated
    const CORE_STAGES_AJAX = 512; // deprecated
    const CORE_STAGES_DECODEURLS = 1024;
    const CORE_STAGES_THEME = 2048;
    const CORE_STAGES_ALL = 4095;

    /**
     * get a configuration variable
     *
     * @param name $ the name of the variable
     * @param default the default value to return if the requested param is not set
     * @return mixed value of the variable, or false on failure
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
     * set a configuration variable
     *
     * @param name $ the name of the variable
     * @param value $ the value of the variable
     * @return bool true on success, false on failure
     */
    public static function setVar($name, $value = '')
    {
        $name = isset($name) ? (string) $name : '';

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
     * delete a configuration variable
     *
     * @param name $ the name of the variable
     * @returns mixed value of deleted config var or false on failure
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
        
        // delete the variable
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
     * Initialise Zikula
     * Carries out a number of initialisation tasks to get Zikula up and
     * running.
     *
     * @returns bool true initialisation successful false otherwise
     */
    public static function init($stages = self::CORE_STAGES_ALL)
    {
        $coreInitEvent = new Zikula_Event('core.init');

        static $globalscleansed = false;

        // force register_globals = off
        if ($globalscleansed == false && ini_get('register_globals') && !self::isInstalling()) {
            foreach ($GLOBALS as $s_variable_name => $m_variable_value) {
                if (!in_array($s_variable_name, array(
                'GLOBALS',
                'argv',
                'argc',
                '_FILES',
                '_COOKIE',
                '_POST',
                '_GET',
                '_SERVER',
                '_ENV',
                '_SESSION',
                '_REQUEST',
                's_variable_name',
                'm_variable_value',
                '_ZSession'))) {
                    unset($GLOBALS[$s_variable_name]);
                }
            }
            unset($GLOBALS['s_variable_name']);
            unset($GLOBALS['m_variable_value']);
            $globalscleansed = true;
        }

        // Neither Smarty nor Zikula itself works with magic_quotes_runtime (not to be confused with magic_quotes_gpc!)
        if (get_magic_quotes_runtime()) {
            die('Sorry, but Zikula does not support PHP magic_quotes_runtime - please disable this feature in your php.ini file.');
        }

        if (!is_numeric($stages)) {
            $stages = self::CORE_STAGES_ALL;
        }

        // initialise environment
        if ($stages & self::CORE_STAGES_CONFIG) {
            if (!self::isInstalling()) {
                $GLOBALS['ZConfig'] = array();
                $GLOBALS['ZRuntime'] = array();
            }
        }

        // store the load stages in a global so other API's can check whats loaded
        $GLOBALS['loadstages'] = $stages;

        EventUtil::notify(new Zikula_Event('core.preinit'));

        // Initialise and load configuration
        if ($stages & self::CORE_STAGES_CONFIG) {
            require 'config/config.php';

            if (self::isInstalling()) {
                $GLOBALS['ZConfig']['System']['Z_CONFIG_USE_OBJECT_ATTRIBUTION'] = false;
                $GLOBALS['ZConfig']['System']['Z_CONFIG_USE_OBJECT_LOGGING'] = false;
                $GLOBALS['ZConfig']['System']['Z_CONFIG_USE_OBJECT_META'] = false;
            }
            if (!isset($GLOBALS['ZConfig']['Multisites'])) {
                $GLOBALS['ZConfig']['Multisites'] = array();
                $GLOBALS['ZConfig']['Multisites']['multi'] = 0;
            }

            // initialise custom event listeners from config.php settings
            $coreInitEvent->setArg('stage', self::CORE_STAGES_CONFIG);
            EventUtil::notify($coreInitEvent);
        }

        // Initialize the (ugly) additional header array
        $GLOBALS['additional_header'] = array();

        if (self::isLegacyMode()) {
            require_once 'lib/legacy/Compat.php';
        }

        /**
         * schemas - holds all component/instance schemas
         * Should wrap this in a static one day, but the information
         * isn't critical so we'll do it later
         */
        $GLOBALS['schemas'] = array();

        // Check that Zikula is installed before continuing
        if (self::getVar('installed') == 0 && !self::isInstalling()) {
            header('HTTP/1.1 503 Service Unavailable');
            if (file_exists('config/templates/notinstalled.htm')) {
                require_once 'config/templates/notinstalled.htm';
            } else {
                require_once 'lib/templates/notinstalled.htm';
            }
            self::shutDown();
        }

        // initialise time to render
        if ($GLOBALS['ZConfig']['Debug']['pagerendertime']) {
            $GLOBALS['ZRuntime']['dbg_starttime'] = microtime(true);
        }

        if ($stages & self::CORE_STAGES_OBJECTLAYER) {
            $coreInitEvent->setArg('stage', self::CORE_STAGES_OBJECTLAYER);
            EventUtil::notify($coreInitEvent);
        }

        if ($stages & self::CORE_STAGES_DB) {
            $connection = null;
            try {
                DBConnectionStack::init();
            } catch (PDOException $e) {
                if (!self::isInstalling()) {
                    header('HTTP/1.1 503 Service Unavailable');
                    $templateFile = '/templates/dbconnectionerror.htm';
                    if (file_exists('config' . $templateFile)) {
                        include 'config' . $templateFile;
                    } else {
                        include 'lib' . $templateFile;
                    }
                    self::shutDown();
                } else {
                    return false;
                }
            }

            $coreInitEvent->setArg('stage', self::CORE_STAGES_DB);
            EventUtil::notify($coreInitEvent);
        }

        if ($stages & self::CORE_STAGES_TABLES) {
            // Initialise dbtables
            $GLOBALS['dbtables'] = isset($pntable) ? $pntable : null;
            // ensure that the base modules info is available
            ModUtil::dbInfoLoad('Modules', 'Modules');
            ModUtil::dbInfoLoad('Settings', 'Settings');
            ModUtil::dbInfoLoad('Theme', 'Theme');
            ModUtil::dbInfoLoad('Users', 'Users');
            ModUtil::dbInfoLoad('Groups', 'Groups');
            ModUtil::dbInfoLoad('Permissions', 'Permissions');
            // load core module vars
            ModUtil::initCoreVars();
            // if we've got this far an error handler can come into play
            // (except in the installer)
            if (!self::isInstalling()) {
                set_error_handler('System::errorHandler');
            }

            $coreInitEvent->setArg('stage', self::CORE_STAGES_TABLES);
            EventUtil::notify($coreInitEvent);
        }

        if ($stages & self::CORE_STAGES_SESSIONS) {
            // Other includes
            // ensure that the sesssions table info is available
            ModUtil::dbInfoLoad('Users', 'Users');
            $anonymoussessions = self::getVar('anonymoussessions');
            if ($anonymoussessions == '1' || !empty($_COOKIE[SessionUtil::getCookieName()])) {
                // we need to create a session for guests as configured or
                // a cookie exists which means we have been here before
                // Start session
                SessionUtil::requireSession();

                // Auto-login via HTTP(S) REMOTE_USER property
                if (self::getVar('session_http_login') && !UserUtil::isLoggedIn()) {
                    UserUtil::loginHttp();
                }
            }

            $coreInitEvent->setArg('stage', self::CORE_STAGES_SESSIONS);
            EventUtil::notify($coreInitEvent);
        }

        // Have to load in this order specifically since we cant setup the languages until we've decoded the URL if required (drak)
        // start block
        if ($stages & self::CORE_STAGES_LANGS) {
            $lang = ZLanguage::getInstance();
        }

        if ($stages & self::CORE_STAGES_DECODEURLS) {
            self::queryStringDecode();
            $coreInitEvent->setArg('stage', self::CORE_STAGES_DECODEURLS);
            EventUtil::notify($coreInitEvent);
        }

        if ($stages & self::CORE_STAGES_LANGS) {
            $lang->setup();
            $coreInitEvent->setArg('stage', self::CORE_STAGES_LANGS);
            EventUtil::notify($coreInitEvent);
        }
        // end block

        // perform some checks that might result in a die() upon failure when we are in development mode
        self::_development_checks();

        if ($stages & self::CORE_STAGES_MODS) {
            // Set compression on if desired
            if (self::getVar('UseCompression') == 1) {
                ob_start("ob_gzhandler");
            }

            if (ModUtil::available('SecurityCenter') && self::getVar('enableanticracker') == 1) {
                ModUtil::apiFunc('SecurityCenter', 'user', 'secureinput');
            }

            $coreInitEvent->setArg('stage', self::CORE_STAGES_MODS);
            EventUtil::notify($coreInitEvent);
        }

        if ($stages & self::CORE_STAGES_THEME) {
            // register default page vars
            PageUtil::registerVar('title');
            PageUtil::registerVar('description', false, self::getVar('slogan'));
            PageUtil::registerVar('keywords', true);
            PageUtil::registerVar('stylesheet', true);
            PageUtil::registerVar('javascript', true);
            PageUtil::registerVar('body', true);
            PageUtil::registerVar('rawtext', true);
            PageUtil::registerVar('footer', true);
            // Load the theme
            Theme::getInstance();
            $coreInitEvent->setArg('stage', self::CORE_STAGES_THEME);
            EventUtil::notify($coreInitEvent);
        }

        // check the users status, if not 1 then log him out
        if (UserUtil::isLoggedIn()) {
            $userstatus = UserUtil::getVar('activated');
            if ($userstatus != 1) {
                UserUtil::logout();
                LogUtil::registerStatus(__('You have been logged out.'));
                $params = ($userstatus == 2) ? array('confirmtou' => 1) : array();
                System::redirect(ModUtil::url('Users', 'user', 'loginscreen', $params));
            }
        }

        EventUtil::notify(new Zikula_Event('core.postinit', null, array('stages' => $stages)));

        // remove log files being too old
        LogUtil::_cleanLogFiles();

        return true;
    }

    /**
     * get a list of database connections
     *
     * @param bool $pass_by_reference default = false
     * @param string $fetchmode set ADODB fetchmode ADODB_FETCH_NUM, ADODB_FETCH_ASSOC, ADODB_FETCH_DEFAULT, ADODB_FETCH_BOTH
     * @return array array of database connections
     */
    public static function dbGetConn($pass_by_reference = false, $fetchmode = Doctrine::HYDRATE_NONE) // TODO A map ADODB fetch modes to Doctrine HYDRATES, e.g. Doctrine::HYDRATE_NONE
    {
        $ret = DBConnectionStack::getConnection($fetchmode);

        // If $pass_by_reference is true, return a reference to the dbconn object
        if ($pass_by_reference == true) {
            return $ret;
        }

        $dbconn = array($ret);
        return $dbconn;
    }

    /**
     * get a list of database tables
     *
     * @return array array of database tables
     */
    public static function dbGetTables()
    {
        return $GLOBALS['dbtables'];
    }

    /**
     * get table prefix
     *
     * get's the database prefix for the current site
     *
     * In a non multisite scenario this will be the 'prefix' config var
     * from config/config.php. For a multisite configuration the multistes
     * module will manage the prefixes for a given table
     *
     * The table name parameter is the table name to get the prefix for
     * minus the prefix and seperating _
     * e.g. pnDBGetPrefix returns pn_modules for pnDBGetPrefix('modules');
     *
     * @param table - table name
     */
    public static function dbGetTablePrefix($table)
    {
        if (!isset($table)) {
            return false;
        }

        return self::getVar('prefix');
    }

    /**
     * strip slashes
     *
     * stripslashes on multidimensional arrays.
     * Used in conjunction with FormUtil::getPassedValue
     *
     * @param any $ variables or arrays to be stripslashed
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
     * validate a zikula variable
     *
     * @param $var   the variable to validate
     * @param $type  the type of the validation to perform (email, url etc.)
     * @param $args  optional array with validation-specific settings (never used...)
     * @return bool true if the validation was successful, false otherwise
     */
    public static function varValidate($var, $type, $args = 0)
    {
        if (!isset($var) || !isset($type)) {
            return false;
        }

        // typecasting (might be useless in this function)
        $var = (string) $var;
        $type = (string) $type;

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
        static $regexp = array( // 'mod'    => '/^[^\\\/\?\*\"\'\>\<\:\|]*$/',
        // 'func'   => '/[^0-9a-zA-Z_]/',
        // 'api'    => '/[^0-9a-zA-Z_]/',
        // 'theme'  => '/^[^\\\/\?\*\"\'\>\<\:\|]*$/',
        'email' => '/^(?:[^\s\000-\037\177\(\)<>@,;:\\"\[\]]\.?)+@(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}$/Ui',
        'url' => '/^([!#\$\046-\073=\077-\132_\141-\172~]|(?:%[a-f0-9]{2}))+$/i');

        // special cases
        if ($type == 'mod' && $var == '/PNConfig') {
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

            if (self::getVar('idnnames') == 1) {
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
            if (strstr($var, chr(160)) || strstr($var, chr(173))) {
                return false;
            }
        }

        // variable passed special checks. We now to generic checkings.

        // check for maximal length
        if (isset($maxlength[$type]) && strlen($var) > $maxlength[$type]) {
            return false;
        }

        // check for minimal length
        if (isset($minlength[$type]) && strlen($var) < $minlength[$type]) {
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
     * get base URI for Zikula
     *
     * @return string base URI for Zikula
     */
    public static function getBaseUri()
    {
        static $path;

        if (!isset($path)) {
            $path = self::serverGetVar('SCRIPT_NAME');
            $path = str_replace(strrchr($path, '/'), '', $path);
        }
        if ($GLOBALS['ZConfig']['Multisites']['multi'] == 1) {
            $path = $GLOBALS['ZConfig']['Multisites']['siteDNS'];
        }
        return $path;
    }

    /**
     * get base URL for Zikula
     *
     * @return string base URL for Zikula
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

        $path = System::getBaseUri();

        return "$proto$server$path/";
    }

    /**
     * get homepage URL for Zikula
     *
     * @return string homepage URL for Zikula
     */
    public static function getHomepageUrl()
    {
        // check the use of friendly url setup
        $shorturls = self::getVar('shorturls', false);
        $dirBased = (self::getVar('shorturlstype') == 0 ? true : false);
        $langRequired = ZLanguage::isRequiredLangParam();

        if ($shorturls && $dirBased) {
            $result = System::getBaseUrl();
            if ($langRequired) {
                $result .= ZLanguage::getLanguageCode();
            }
        } else  {
            $result = self::getVar('entrypoint', 'index.php');
            if (ZLanguage::isRequiredLangParam()) {
                $result .= '?lang=' . ZLanguage::getLanguageCode();
            }
        }

        return $result;
    }

    /**
     * Carry out a redirect
     *
     * @param string $redirecturl URL to redirect to
     * @param array $addtionalheaders array of header strings to send with redirect
     * @returns bool true if redirect successful, false otherwise
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
        if (headers_sent()) {
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
            $baseurl = System::getBaseUrl();
            $redirecturl = $baseurl . $redirecturl;
        }

        header("Location: $redirecturl",true,(int)$type);

        return true;
    }

    /**
     * check to see if this is a local referral
     *
     * @param bool strict - strict checking ensures that a referer must be set as well as local
     * @return bool true if locally referred, false if not
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
     * send an email
     *
     * e-mail messages should now be send with a ModUtil::apiFunc call to the mailer module
     *
     * @deprecated
     * @param to $ - recipient of the email
     * @param subject $ - title of the email
     * @param message $ - body of the email
     * @param headers $ - extra headers for the email
     * @param html $ - message is html formatted
     * @param debug $ - if 1, echo mail content
     * @return bool true if the email was sent, false if not
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
     * Gets a server variable
     *
     * Returns the value of $name from $_SERVER array.
     * Accepted values for $name are exactly the ones described by the
     * {@link http://www.php.net/manual/en/reserved.variables.html#reserved.variables.server PHP manual}.
     * If the server variable doesn't exist void is returned.
     *
     * @param name string the name of the variable
     * @param default the default value to return if the requested param is not set
     * @return mixed value of the variable
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
     * Gets the host name
     *
     * Returns the server host name fetched from HTTP headers when possible.
     * The host name is in the canonical form (host + : + port) when the port is different than 80.
     *
     * @return string HTTP host name
     */
    public static function getHost()
    {
        $server = self::serverGetVar('HTTP_HOST');
        if (empty($server)) {
            // HTTP_HOST is reliable only for HTTP 1.1
            $server = self::serverGetVar('SERVER_NAME');
            $port = self::serverGetVar('SERVER_PORT');
            if ($port != '80')
                $server .= ":$port";
        }
        return $server;
    }

    /**
     * Get current URI (and optionally add/replace some parameters)
     *
     * @access public
     * @param args array additional parameters to be added to/replaced in the URI (e.g. theme, ...)
     * @return string current URI
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
     * Gets the current protocol
     *
     * Returns the HTTP protocol used by current connection, it could be 'http' or 'https'.
     *
     * @return string current HTTP protocol
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
     * Get current URL
     *
     * @access public
     * @param args array additional parameters to be added to/replaced in the URL (e.g. theme, ...)
     * @return string current URL
     * @todo cfr. BaseURI() for other possible ways, or try PHP_SELF
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
     * Decode the path string into a set of variable/value pairs
     *
     * This API works in conjunction with the new short urls
     * system to extract a path based variable set into the Get, Post
     * and request superglobals.
     * A sample path is /modname/function/var1:value1
     *
     */
    public static function queryStringDecode()
    {
        if (self::isInstalling()) {
            return;
        }

        // get our base parameters to work out if we need to decode the url
        $module = FormUtil::getPassedValue('module', null, 'GETPOST');
        $func = FormUtil::getPassedValue('func', null, 'GETPOST');
        $type = FormUtil::getPassedValue('type', null, 'GETPOST');

        // check if we need to decode the url
        if ((self::getVar('shorturls') && self::getVar('shorturlstype') == 0 && (empty($module) && empty($type) && empty($func)))) {
            // define our site entry points
            $customentrypoint = self::getVar('entrypoint');
            $root = empty($customentrypoint) ? 'index.php' : $customentrypoint;
            $tobestripped = array(
                    "/$root",
                    '/admin.php',
                    '/user.php',
                    '/error.php',
                    System::getBaseUri());

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

                // first try the first argument as a module
                $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($args[1]));
                // if that fails it's a theme
                if (!$modinfo) {
                    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args[1]));
                    if ($themeinfo) {
                        self::queryStringSetVar('theme', $themeinfo['name']);
                        // now shift the vars and continue as before
                        array_shift($args);
                        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($args[1]));
                    } else {
                        // add the default module handler into the code
                        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName(self::getVar('shorturlsdefaultmodule')));
                        array_unshift($args, $modinfo['url']);
                    }
                }
                self::queryStringSetVar('module', $modinfo['name']);
                // the function name is the second argument string
                isset($args[2]) ? self::queryStringSetVar('func', $args[2]) : null;
                $modname = FormUtil::getPassedValue('module', null, 'GETPOST');
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
     * add a variable/value pair into the query string
     * (really the _GET superglobal
     * This API also adds the variable to the _REQUEST superglobal for consistentcy
     *
     * @return bool true if successful, false otherwise
     */
    public static function queryStringSetVar($name, $value)
    {
        if (!isset($name)) {
            return;
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
     *
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $event = new Zikula_Event('systemerror', null, array('errorno' => $errno, 'errstr' => $errstr, 'errfile' => $errfile, 'errline' => $errline, 'errcontext' => $errcontext));
        EventUtil::notify($event);

        // check for an @ suppression
        if (error_reporting() == 0 || (defined('E_DEPRECATED') && $errno == E_DEPRECATED || $errno == E_STRICT)) {
            return;
        }

        static $errorlog, $errorlogtype, $errordisplay, $ztemp;
        if (!isset($errorlogtype)) {
            $errorlog = self::getVar('errorlog');
            $errorlogtype = self::getVar('errorlogtype');
            $errordisplay = self::getVar('errordisplay');
            $ztemp = DataUtil::formatForOS(self::getVar('temp'), true);
        }

        // What do we want to log?
        // 1 - Log real errors only.  2 - Log everything
        $logError = ($errorlog == 2 || ($errorlog == 1 && ($errno != E_WARNING && $errno != E_NOTICE && $errno != E_USER_WARNING && $errno != E_USER_NOTICE)));
        if ($logError == true) {
            // log the error
            $msg = "Zikula Error: $errstr";
            if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
                $request = self::getCurrentUri();
                $msg .= " in $errfile on line $errline for page $request";
            }
            switch ($errorlogtype) {
                // log to the system log (default php handling....)
                case 0:
                    error_log($msg);
                    break;
                // e-mail the error
                case 1:
                    $toaddress = self::getVar('errormailto');
                    $body = ModUtil::func('Errors', 'user', 'system', array(
                            'type' => $errno,
                            'message' => $errstr,
                            'file' => $errfile,
                            'line' => $errline));
                    ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array(
                            'toaddress' => $toaddress,
                            'toname' => $toaddress,
                            'subject' => __('Error! Oh! Wow! An \'unidentified system error\' has occurred.'),
                            'body' => $body));
                    break;
                // log a module specific log (based on top level module)
                case 2:
                    $modname = DataUtil::formatForOS(ModUtil::getName());
                    error_log($msg . "\r\n", 3, $ztemp . '/error_logs/' . $modname . '.log');
                    break;
                // log to global error log
                case 3:
                    error_log($msg . "\r\n", 3, $ztemp . '/error_logs/error.log');
                    break;
            }
        }

        // should we display the error to the user
        if ($errordisplay == 0) {
            return;
        }

        // check if we want to flag up warnings and notices
        if ($errordisplay == 1 && ($errno == E_WARNING || $errno == E_NOTICE || $errno == E_USER_WARNING || $errno == E_USER_NOTICE)) {
            return;
        }

        // clear the output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // display the new output and halt the script
        header('HTTP/1.0 500 System Error');
        echo ModUtil::func('Errors', 'user', 'system', array(
        'type' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline));
        Theme::getInstance()->themefooter();
        self::shutDown();
    }

    /**
     * Gracefully shut down the framework (traps all exit and die calls)
     *
     * @param $exit_param params to pass to the exit function
     * @return none - function halts execution
     *
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
     * When in development mode, perform some checks that might result in a die() upon failure
     *
     * TODO D: extend this when needed
     *
     * @param none
     * @return none - function halts execution if needed
     *
     */
    public static function _development_checks()
    {
        if ($GLOBALS['ZConfig']['System']['development'] == 1 && !self::isInstalling()) {
            $die = false;

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

            // Mailer needs fsockopen()
            if (ModUtil::available('Mailer') && !function_exists('fsockopen')) {
                echo __("Error! The PHP function 'fsockopen()' is needed within the Zikula mailer module, but is not available.");
                $die = true;
            }

            $temp = DataUtil::formatForOS(self::getVar('temp'), true) . '/';
            $folders = array(
                    $temp,
                    $temp . 'error_logs',
                    $temp . 'Renderer_compiled',
                    $temp . 'Renderer_cache',
                    $temp . 'Theme_compiled',
                    $temp . 'Theme_cache');
            if (ModUtil::available('Feeds')) {
                $folders[] = $temp . 'feeds';
            }

            foreach ($folders as $folder) {
                if (!is_writable($folder)) {
                    echo __f("Error! Stop, please! '%s' was not found, or else the server does not have write permission for it.", $folder) . '<br />';
                    $die = true;
                }
            }

            if ($die == true) {
                die('<br /><br />' . __("This message is shown to you, the Administrator, because the system is in development mode (\$ZConfig['System']['development'] = 1; in config/config.php). This helps you to avoid common problems with Zikula."));
            }
        }
    }

    public static function isInstalling()
    {
        return (bool)defined('_ZINSTALLVER');
    }

    public static function isLegacyMode()
    {
        if (!isset($GLOBALS['ZConfig']['System']['compat_layer'])) {
            return false;
        }
        return (bool)$GLOBALS['ZConfig']['System']['compat_layer'];
    }

    public static function hasLegacyTemplates()
    {
        if (!isset($GLOBALS['ZConfig']['System']['legacy_prefilters'])) {
            return false;
        }
        return (bool)$GLOBALS['ZConfig']['System']['legacy_prefilters'];
    }

    public static function isDevelopmentMode()
    {
        if (!isset($GLOBALS['ZConfig']['System']['development'])) {
            return false;
        }
        return (bool)$GLOBALS['ZConfig']['System']['development'];
    }
}