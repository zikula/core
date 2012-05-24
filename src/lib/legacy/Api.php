<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Legacy
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Core version informations - should be upgraded on each release for
 * better control on config settings
 */
define('PN_VERSION_NUM', Zikula_Core::VERSION_NUM);
define('PN_VERSION_ID', Zikula_Core::VERSION_ID);
define('PN_VERSION_SUB', Zikula_Core::VERSION_SUB);

/**
 * Yes/no integer
 */
define('PNYES', 1);
define('PNNO', 0);

/**
 * State of modules
 */
define('PNMODULE_STATE_UNINITIALISED', 1);
define('PNMODULE_STATE_INACTIVE', 2);
define('PNMODULE_STATE_ACTIVE', 3);
define('PNMODULE_STATE_MISSING', 4);
define('PNMODULE_STATE_UPGRADED', 5);
define('PNMODULE_STATE_NOTALLOWED', 6);
define('PNMODULE_STATE_INVALID', -1);
define('MODULE_TYPE_MODULE', 2);
define('MODULE_TYPE_SYSTEM', 3);

/**
 * Module dependency states
 */
define('PNMODULE_DEPENDENCY_REQUIRED', 1);
define('PNMODULE_DEPENDENCY_RECOMMENDED', 2);
define('PNMODULE_DEPENDENCY_CONFLICTS', 3);

/**
 * 'All' and 'unregistered' for user and group permissions
 */
define('PNPERMS_ALL', SecurityUtil::PERMS_ALL);
define('PNPERMS_UNREGISTERED', SecurityUtil::PERMS_UNREGISTERED);

/**
 * Fake module for config vars
 */
define('PN_CONFIG_MODULE', 'ZConfig');

/**
 *  Theme filters
 */
define('PNTHEME_FILTER_ALL', 0);
define('PNTHEME_FILTER_USER', 1);
define('PNTHEME_FILTER_SYSTEM', 2);
define('PNTHEME_FILTER_ADMIN', 3);

/**
 *  Theme states
 */
define('PNTHEME_STATE_ALL', 0);
define('PNTHEME_STATE_ACTIVE', 1);
define('PNTHEME_STATE_INACTIVE', 2);

/**
 *  Theme types
 */
define('PNTHEME_TYPE_ALL', 0);
define('PNTHEME_TYPE_XANTHIA3', 3);

/**
 * Core initialisation stages
 */
define('PN_CORE_NONE', 0);
define('PN_CORE_CONFIG', 1);
define('PN_CORE_ADODB', 2); // deprecated
define('PN_CORE_DB', 4);
define('PN_CORE_OBJECTLAYER', 8);
define('PN_CORE_TABLES', 16);
define('PN_CORE_SESSIONS', 32);
define('PN_CORE_LANGS', 64);
define('PN_CORE_MODS', 128);
define('PN_CORE_TOOLS', 256); // deprecated
define('PN_CORE_AJAX', 512); // deprecated
define('PN_CORE_DECODEURLS', 1024);
define('PN_CORE_THEME', 2048);
define('PN_CORE_ALL', 4095);

/**
 * get a configuration variable
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::getVar()
 *
 * @param name $ the name of the variable
 * @param default the default value to return if the requested param is not set
 * @return mixed value of the variable, or false on failure
 */
function pnConfigGetVar($name, $default = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::getVar')), E_USER_DEPRECATED);

    return System::getVar($name, $default);
}

/**
 * set a configuration variable
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::setVar()
 *
 * @param name $ the name of the variable
 * @param value $ the value of the variable
 * @return bool true on success, false on failure
 */
function pnConfigSetVar($name, $value = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::setVar')), E_USER_DEPRECATED);

    return System::setVar($name, $value);
}

/**
 * delete a configuration variable
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::delVar()
 *
 * @param name $ the name of the variable
 * @returns mixed value of deleted config var or false on failure
 */
function pnConfigDelVar($name)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::delVar')), E_USER_DEPRECATED);

    return System::delVar($name);
}

/**
 * Initialise Zikula
 * Carries out a number of initialisation tasks to get Zikula up and
 * running.
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::init()
 *
 * @returns bool true initialisation successful false otherwise
 */
function pnInit($stages = PN_CORE_ALL)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::init')), E_USER_DEPRECATED);

    return System::init($stages);
}

/**
 * get a list of database connections
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see DBConnectionStack::getConnection()
 *
 * @param bool $pass_by_reference default = false
 * @param string $fetchmode set ADODB fetchmode ADODB_FETCH_NUM, ADODB_FETCH_ASSOC, ADODB_FETCH_DEFAULT, ADODB_FETCH_BOTH
 * @return array array of database connections
 */
function pnDBGetConn($pass_by_reference = false, $fetchmode = Doctrine::HYDRATE_NONE)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'Doctrine_Manager::getInstance()->getCurrentConnection();')), E_USER_DEPRECATED);

    return Doctrine_Manager::getInstance()->getCurrentConnection();
}

/**
 * get a list of database tables
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see DBUtil::dbGetTables()
 *
 * @return array array of database tables
 */
function pnDBGetTables()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'DBUtil::getTables')), E_USER_DEPRECATED);

    return DBUtil::getTables();
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
 * @deprecated Deprecated since version 1.3.0.
 * @see DBUtil::getTablePrefix()
 *
 * @param table - table name
 */
function pnDBGetTablePrefix($table)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'DBUtil::getTablePrefix')), E_USER_DEPRECATED);

    return DBUtil::getTablePrefix($table);
}

/**
 * strip slashes
 *
 * stripslashes on multidimensional arrays.
 * Used in conjunction with pnVarCleanFromInput
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::stripslashes()
 *
 * @param any $ variables or arrays to be stripslashed
 */
function pnStripslashes(&$value)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::stripslashes')), E_USER_DEPRECATED);
    System::stripslashes($value);
}

/**
 * validate a zikula variable
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::varValidate()
 *
 * @param $var   the variable to validate
 * @param $type  the type of the validation to perform (email, url etc.)
 * @param $args  optional array with validation-specific settings (never used...)
 * @return bool true if the validation was successful, false otherwise
 */
function pnVarValidate($var, $type, $args = 0)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::varValidate')), E_USER_DEPRECATED);

    return System::varValidate($var, $type, $args);
}

/**
 * get base URI for Zikula
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::getBaseUri()
 *
 * @return string base URI for Zikula
 */
function pnGetBaseURI()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::getBaseUri')), E_USER_DEPRECATED);

    return System::getBaseUri();
}

/**
 * get base URL for Zikula
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::getBaseUrl()
 *
 * @return string base URL for Zikula
 */
function pnGetBaseURL()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::getBaseUrl')), E_USER_DEPRECATED);

    return System::getBaseUrl();
}

/**
 * get homepage URL for Zikula
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::getHomepageUrl()
 *
 * @return string homepage URL for Zikula
 */
function pnGetHomepageURL()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::getHomepageUrl')), E_USER_DEPRECATED);

    return System::getHomepageUrl();
}

/**
 * Carry out a redirect
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::redirect()
 *
 * @param string $redirecturl URL to redirect to
 * @param array $addtionalheaders array of header strings to send with redirect
 * @returns bool true if redirect successful, false otherwise
 */
function pnRedirect($redirecturl, $additionalheaders = array())
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::redirect')), E_USER_DEPRECATED);

    return System::redirect($redirecturl, $additionalheaders);
}

/**
 * check to see if this is a local referral
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::localReferer()
 *
 * @param bool strict - strict checking ensures that a referer must be set as well as local
 * @return bool true if locally referred, false if not
 */
function pnLocalReferer($strict = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::localReferer')), E_USER_DEPRECATED);

    return System::localReferer($strict);
}

/**
 * send an email
 *
 * e-mail messages should now be send with a ModUtil::apiFunc call to the mailer module
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::mail()
 *
 * @param to $ - recipient of the email
 * @param subject $ - title of the email
 * @param message $ - body of the email
 * @param headers $ - extra headers for the email
 * @param html $ - message is html formatted
 * @param debug $ - if 1, echo mail content
 * @return bool true if the email was sent, false if not
 */
function pnMail($to, $subject, $message = '', $headers = '', $html = 0, $debug = 0, $altbody = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::mail')), E_USER_DEPRECATED);

    return System::mail($to, $subject, $message, $headers, $html, $debug, $altbody);
}

/**
 * Gets a server variable
 *
 * Returns the value of $name from $_SERVER array.
 * Accepted values for $name are exactly the ones described by the
 * {@link http://www.php.net/manual/en/reserved.variables.html#reserved.variables.server PHP manual}.
 * If the server variable doesn't exist void is returned.
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::serverGetVar()
 *
 * @param name string the name of the variable
 * @param default the default value to return if the requested param is not set
 * @return mixed value of the variable
 */
function pnServerGetVar($name, $default = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::serverGetVar')), E_USER_DEPRECATED);

    return System::serverGetVar($name, $default);
}

/**
 * Gets the host name
 *
 * Returns the server host name fetched from HTTP headers when possible.
 * The host name is in the canonical form (host + : + port) when the port is different than 80.
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::getHost()
 *
 * @return string HTTP host name
 */
function pnGetHost()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::getHost')), E_USER_DEPRECATED);

    return System::getHost();
}

/**
 * Get current URI (and optionally add/replace some parameters)
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::getCurrentUri()
 *
 * @access public
 * @param args array additional parameters to be added to/replaced in the URI (e.g. theme, ...)
 * @return string current URI
 */
function pnGetCurrentURI($args = array())
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::getCurrentUri')), E_USER_DEPRECATED);

    return System::getCurrentUri($args);
}

/**
 * Gets the current protocol
 *
 * Returns the HTTP protocol used by current connection, it could be 'http' or 'https'.
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::serverGetProtocol()
 *
 * @return string current HTTP protocol
 */
function pnServerGetProtocol()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::serverGetProtocol')), E_USER_DEPRECATED);

    return System::serverGetProtocol();
}

/**
 * Get current URL
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::getCurrentUrl()
 *
 * @access public
 * @param args array additional parameters to be added to/replaced in the URL (e.g. theme, ...)
 * @return string current URL
 * @todo cfr. BaseURI() for other possible ways, or try PHP_SELF
 */
function pnGetCurrentURL($args = array())
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::getCurrentUrl')), E_USER_DEPRECATED);

    return System::getCurrentUrl($args);
}

/**
 * Decode the path string into a set of variable/value pairs
 *
 * This API works in conjunction with the new short urls
 * system to extract a path based variable set into the Get, Post
 * and request superglobals.
 * A sample path is /modname/function/var1:value1
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::queryStringDecode()
 *
 */
function pnQueryStringDecode()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::queryStringDecode')), E_USER_DEPRECATED);

    return System::queryStringDecode();
}

/**
 * add a variable/value pair into the query string
 * (really the _GET superglobal
 * This API also adds the variable to the _REQUEST superglobal for consistentcy
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::queryStringSetVar()
 *
 * @return bool true if successful, false otherwise
 */
function pnQueryStringSetVar($name, $value)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::queryStringSetVar')), E_USER_DEPRECATED);

    return System::queryStringSetVar($name, $value);
}

/**
 * @deprecated Deprecated since version 1.3.0.
 * @see System::errorHandler()
 *
 */
function pnErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::errorHandler')), E_USER_DEPRECATED);

    return System::errorHandler($errno, $errstr, $errfile, $errline, $errcontext);
}

/**
 * Gracefully shut down the framework (traps all exit and die calls)
 *
 * @deprecated Deprecated since version 1.3.0.
 * @see System::shutDown()
 *
 * @param $exit_param params to pass to the exit function
 * @return none - function halts execution
 *
 */
function pnShutDown($exit_param = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'System::shutDown')), E_USER_DEPRECATED);

    return System::shutDown($exit_param);
}
