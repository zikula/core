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

class SecurityCenter_Api_User extends AbstractApi
{
    /**
     * Get all hack attempts in db.
     *
     * @param array $args All parameters for the function.
     *                    int $args['startnum'] The start number for the record set.
     *                    int $args['numitems'] Number of items to get.
     *
     * @return  array|bool  Array of items, or false on failure.
     */
    public function getall($args)
    {
        // Optional arguments.
        if (!isset($args['startnum'])) {
            $args['startnum'] = 1;
        }
        if (!isset($args['numitems'])) {
            $args['numitems'] = -1;
        }

        if ((!is_numeric($args['startnum'])) ||
                (!is_numeric($args['numitems']))) {
            return LogUtil::registerArgsError();
        }

        $items = array();

        // Security check
        if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_READ)) {
            return $items;
        }

        // define the permission filter to apply
        $permFilter = array(array('realm'          => 0,
                        'component_left' => 'SecurityCenter',
                        'instance_left'  => 'hid',
                        'instance_right' => 'hacktime',
                        'level'          => ACCESS_READ));

        // get the items from the db
        $items = DBUtil::selectObjectArray('sc_anticracker', '', 'hid', $args['startnum']-1, $args['numitems'], '', $permFilter);

        if ($items === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        // Return the items
        return $items;
    }

    /**
     * Get a specific hack attempt.
     *
     * @param array $args All parameters for the function.
     *                    int $args['hid'] Id of hack item to get.
     *
     * @return  array|bool  Item array, or false on failure.
     */
    public function get($args)
    {
        // Argument check
        if (!isset($args['hid']) || !is_numeric($args['hid'])) {
            return LogUtil::registerArgsError();
        }

        // define the permission filter to apply
        $permFilter = array(array('realm'          => 0,
                        'component_left' => 'SecurityCenter',
                        'instance_left'  => 'hid',
                        'instance_right' => 'hacktime',
                        'level'          => ACCESS_READ));

        return DBUtil::selectObjectByID('sc_anticracker', $args['hid'], 'hid');
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @return int Number of items held by this module.
     */
    public function countitems()
    {
        return DBUtil::selectObjectCount('sc_anticracker');
    }

    /**
     * Protects against basic attempts of Cross-Site Scripting (XSS).
     *
     * @see    http://technicalinfo.net/papers/CSS.html
     *
     * @return void
     */
    public function secureinput()
    {
        if (System::getVar('enableanticracker')) {

            // Lets now sanitize the GET vars
            if (System::getVar('filtergetvars') == 1) {
                if (count($_GET) > 0) {
                    foreach ($_GET as $key => $secvalue) {
                        if (!is_array($secvalue)) {
                            if ($this->_checkinputvalue($secvalue)) {
                                $this->loghackattempt(array('detecting_file' => 'SecurityCenter',
                                        'detecting_line' => __LINE__,
                                        'hacktype' => 'SecurityCenter Alert',
                                        'message' => 'GET: '.$key.'=>'.$secvalue));
                                Header('Location: ' . System::getVar('entrypoint', 'index.php'));
                            }
                        }
                    }
                }
            }

            // Lets now sanitize the POST vars
            if (System::getVar('filterpostvars') == 1) {
                if (count($_POST) > 0) {
                    foreach ($_POST as $key => $secvalue) {
                        if (!is_array($secvalue)) {
                            if ($this->_checkinputvalue($secvalue)) {
                                $this->loghackattempt(array('detecting_file' => 'SecurityCenter',
                                        'detecting_line' => __LINE__,
                                        'hacktype' => 'SecurityCenter Alert',
                                        'message' => 'POST: '.$key.'=>'.$secvalue));
                                Header('Location: ' . System::getVar('entrypoint', 'index.php'));
                            }
                        }
                    }
                }
            }

            // Lets now sanitize the COOKIE vars
            if (System::getVar('filtercookievars') == 1) {
                if (count($_COOKIE) > 0) {
                    foreach ($_COOKIE as $secvalue) {
                        if (!is_array($secvalue)) {
                            if ($this->_checkinputvalue($secvalue)) {
                                $this->loghackattempt(array('detecting_file' => 'SecurityCenter',
                                        'detecting_line' => __LINE__,
                                        'hacktype' => 'SecurityCenter Alert',
                                        'message' => 'COOKIE: '.$key.'=>'.$secvalue));
                                Header('Location: ' . System::getVar('entrypoint', 'index.php'));
                            }
                        }
                    }
                }
            }

            // Run IDS if desired
            if (System::getVar('useids') == 1) {
                try {
                    // build request array defining what to scan
                    // @todo: change the order of the arrays to merge if ini_get('variables_order') != 'EGPCS'
                    if (isset($_REQUEST)) {
                        $request['REQUEST'] = $_REQUEST;
                    }
                    if (isset($_GET)) {
                        $request['GET'] = $_GET;
                    }
                    if (isset($_POST)) {
                        $request['POST'] = $_POST;
                    }
                    if (isset($_COOKIE)) {
                        $request['COOKIE'] = $_COOKIE;
                    }
                    if (isset($_SERVER['HTTP_HOST'])) {
                        $request['HOST'] = $_SERVER['HTTP_HOST'];
                    }
                    if (isset($_SERVER['HTTP_ACCEPT'])) {
                        $request['ACCEPT'] = $_SERVER['HTTP_ACCEPT'];
                    }
                    if (isset($_SERVER['USER_AGENT'])) {
                        $request['USER_AGENT'] = $_SERVER['USER_AGENT'];
                    }
                    // while i think that REQUEST_URI is unnecessary,
                    // the REFERER would be important, but results in way too many false positives
/*                    if (isset($_SERVER['REQUEST_URI'])) {
                        $request['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
                    }
                    if (isset($_SERVER['HTTP_REFERER'])) {
                        $request['REFERER'] = $_SERVER['HTTP_REFERER'];
                    }
*/

                    // initialise configuration object
                    $init = IDS_Init::init();

                    // set configuration options
                    $init->config = $this->_getidsconfig();

                    // create new IDS instance
                    $ids = new IDS_Monitor($request, $init);

                    // run the request check and fetch the results
                    $result = $ids->run();

                    // analyze the results
                    if (!$result->isEmpty()) {
                        // process the IDS_Report object
                        $this->_processIdsResult($init, $result);
                    } else {
                        // no attack detected
                    }
                } catch (Exception $e) {
                    // sth went wrong - maybe the filter rules weren't found
                    // @todo: handle together with own exceptions (Guite)
                    z_exit($this->__f('An error occured during executing PHPIDS: %s', $e->getMessage()));
                }
            }
        }
    }

    /**
     * Log hack attempt.
     *
     * @param array $args All parameters for the function.
     *                    string  $args['detecting_file'] File the hack attempt comes from.
     *                    int     $args['detecting_line'] Line in detecting_file.
     *                    string  $args['hacktype']       Type of the hack.
     *                    string  $args['message']        Info/message about the hack.
     *
     * @return void
     */
    public function loghackattempt($args)
    {
        if (System::getVar('enableanticracker')) {
            if (System::getVar('loghackattempttodb')) {
                $this->loghackattempttodb($args);
            }
            if (System::getVar('emailhackattempt')) {
                $this->mailhackattempt($args);
            }
        }
    }

    /**
     * Logs hack attempt in the database.
     *
     * @param array $args All parameters for the function.
     *                    array   $args                   Full set of http post, get etc. arguments.
     *                    string  $args['detecting_file'] File the hack attempt comes from.
     *                    int     $args['detecting_line'] Line in detecting_file.
     *                    string  $args['hacktype']       Type of the hack.
     *                    string  $args['message']        Info/message about the hack.
     *
     * @return bool True if successful, false otherwise
     */
    public function loghackattempttodb($args)
    {
        $pntable = System::dbGetTables();
        $anticrackerColumn = $pntable['sc_anticracker_column'];

        $hacktime = time();

        $hackfile = isset($args['detecting_file']) ? $args['detecting_file'] : '(no filename available)';
        $hackline = isset($args['detecting_line']) ? $args['detecting_line'] : 0;
        $hacktype = isset($args['hacktype'])       ? $args['hacktype']       : '(no type given)';
        $hackinfo = isset($args['message'])        ? $args['message']        : '(no message given)';

        if (UserUtil::isLoggedIn()) {
            $userid = UserUtil::getVar('uid');
        } else {
            $userid = 0;
        }

        $browser = (array)@get_browser();
        // browser_name_regex might break serialization and is not usefull anyway
        unset($browser['browser_name_regex']);
        // add at least some information for enviroments without browscap.ini
        $browser['HTTP_USER_AGENT']=System::serverGetVar('HTTP_USER_AGENT');
        $browser['HTTP_CLIENT_IP']=System::serverGetVar('HTTP_CLIENT_IP');
        $browser['REMOTE_ADDR']=System::serverGetVar('REMOTE_ADDR');
        $browser['GetHostByName']=GetHostByName(System::serverGetVar( 'REMOTE_ADDR' ));
        $browserinfo = serialize($browser);

        $requestarray = serialize($_REQUEST);
        $getarray = serialize($_GET);
        $postarray = serialize($_POST);
        $serverarray = serialize($_SERVER);
        $envarray = serialize($_ENV);
        $cookiearray = serialize($_COOKIE);
        $filesarray = serialize($_FILES);
        $sessionarray = serialize($_SESSION);

        // Add item to db
        $obj = array('hacktime'     => $hacktime,
                'hackfile'     => $hackfile,
                'hackline'     => $hackline,
                'hacktype'     => $hacktype,
                'hackinfo'     => $hackinfo,
                'userid'       => $userid,
                'browserinfo'  => $browserinfo,
                'requestarray' => $requestarray,
                'getarray'     => $getarray,
                'postarray'    => $postarray,
                'serverarray'  => $serverarray,
                'envarray'     => $envarray,
                'cookiearray'  => $cookiearray,
                'filesarray'   => $filesarray,
                'sessionarray' => $sessionarray
        );

        $result = DBUtil::insertObject($obj, 'sc_anticracker', 'hid');

        if (!$result) {
            return LogUtil::registerError($this->__('Error! Could not create the new record.'));
        }

        // Let any hooks know that we have created a new item.
        ModUtil::callHooks('item', 'create', $obj, array('module' => 'SecurityCenter'));

        // Return the id of the newly created item to the calling process
        return true;
    }

    /**
     * E-mail hack attempt.
     *
     * @param array $args All parameters for the function.
     *                    string  $args['detecting_file'] File the hack attempt comes from.
     *                    int     $args['detecting_line'] Line in detecting_file.
     *                    string  $args['hacktype']       Type of the hack.
     *                    string  $args['message']        Info/message about the hack.
     *
     * @return void
     */
    public function mailhackattempt($args)
    {
        // get contents of mail message
        $summarycontent = System::getVar('summarycontent');
        $fullcontent = System::getVar('fullcontent');

        // substitute placeholders in summary content with real values
        $summarycontent = preg_replace('/%sitename%/i', System::getVar('sitename'), $summarycontent);
        $summarycontent = preg_replace('/%date%/i', DateUtil::strftime( $this->__('%b %d, %Y'), (time())), $summarycontent);
        $summarycontent = preg_replace('/%time%/i', DateUtil::strftime( $this->__('%I:%M %p'), (time())), $summarycontent);
        $summarycontent = preg_replace('/%filename%/i', $args['detecting_file'], $summarycontent);
        $summarycontent = preg_replace('/%linenumber%/i', strval($args['detecting_line']), $summarycontent);
        $summarycontent = preg_replace('/%type%/i', System::getVar('sitename'), $summarycontent);
        $summarycontent = preg_replace('/%additionalinfo%/i', $args['message'], $summarycontent);

        if (UserUtil::isLoggedIn()) {
            $summarycontent = preg_replace('/%username%/i', UserUtil::getVar('uname'), $summarycontent);
            $summarycontent = preg_replace('/%useremail%/i', UserUtil::getVar('email'), $summarycontent);
            $summarycontent = preg_replace('/%userrealname%/i', UserUtil::getVar('name'), $summarycontent);
        } else {
            $summarycontent = preg_replace('/%username%/i', System::getVar('anonymous'), $summarycontent);
            $summarycontent = preg_replace('/%useremail%/i', '-', $summarycontent);
            $summarycontent = preg_replace('/%userrealname%/i', '-', $summarycontent);
        }

        $summarycontent = preg_replace('/%httpclientip%/i', System::serverGetVar('HTTP_CLIENT_IP'), $summarycontent);
        $summarycontent = preg_replace('/%remoteaddr%/i', System::serverGetVar('REMOTE_ADDR'), $summarycontent);
        $summarycontent = preg_replace('/%gethostbyremoteaddr%/i', GetHostByName(System::serverGetVar( 'REMOTE_ADDR' )), $summarycontent);

        $bodytext = $summarycontent;

        // if full mail is requested then add additional info
        if (System::getVar('onlysendsummarybyemail') == 0) {
            //initalise output string
            $output = '';
            // build output
            while (list($key, $value) = each($_REQUEST)) {
                $output .= "REQUEST * $key : $value\n";
            }

            // replace placeholder with output array
            $fullcontent = preg_replace('/%requestarray%/i', $output, $fullcontent);

            //initalise output string
            $output = '';
            // build output
            while (list($key, $value) = each($_GET)) {
                $output .= "GET * $key : $value\n";
            }
            // replace placeholder with output array
            $fullcontent = preg_replace('/%getarray%/i', $output, $fullcontent);

            //initalise output string
            $output = '';
            // build output
            while (list($key, $value) = each($_POST)) {
                $output .= "POST * $key : $value\n";
            }
            // replace placeholder with output array
            $fullcontent = preg_replace('/%postarray%/i', $output, $fullcontent);

            //initalise output string
            $output = '';
            // build output
            $output .= "HTTP_USER_AGENT: ".$_SERVER['HTTP_USER_AGENT']."\n";
            $browser = (array)get_browser();
            while (list($key, $value) = each($browser)) {
                $output .= "BROWSER * $key : $value\n";
            }
            // replace placeholder with output array
            $fullcontent = preg_replace('/%browserarray%/i', $output, $fullcontent);

            //initalise output string
            $output = '';
            // build output
            while (list($key, $value) = each( $_SERVER)) {
                $output .= "SERVER * $key : $value\n";
            }
            // replace placeholder with output array
            $fullcontent = preg_replace('/%requestarray%/i', $output, $fullcontent);

            //initalise output string
            $output = '';
            // build output
            while (list($key, $value) = each( $_ENV)) {
                $output .= "ENV * $key : $value\n";
            }
            // replace placeholder with output array
            $fullcontent = preg_replace('/%envarray%/i', $output, $fullcontent);

            //initalise output string
            $output = '';
            // build output
            while (list($key, $value) = each($_COOKIE)) {
                $output .= "COOKIE * $key : $value\n";
            }
            // replace placeholder with output array
            $fullcontent = preg_replace('/%cookiearray%/i', $output, $fullcontent);

            //initalise output string
            $output = '';
            // build output
            while (list($key, $value) = each($_FILES)) {
                $output .= "FILES * $key : $value\n";
            }
            // replace placeholder with output array
            $fullcontent = preg_replace('/%filesarray%/i', $output, $fullcontent);

            //initalise output string
            $output = '';
            // build output
            while (list($key, $value) = each($_SESSION)) {
                $output .= "SESSION * $key : $value\n";
            }
            // replace placeholder with output array
            $fullcontent = preg_replace('/%filesarray%/i', $output, $fullcontent);

            $bodytext = $bodytext . $fullcontent;
        }

        // construct and send email
        $sitename = System::getVar('sitename');
        $adminmail = System::getVar('adminmail');
        $headers = "From: $sitename <$adminmail>\n"
                ."X-Priority: 1 (Highest)";
        System::mail($adminmail, $this->__f('Possible attempt to crack your site (type: %s)', $args['hacktype']), $bodytext, $headers );

        return;
    }

    /**
     * Retrieves an instance of HTMLPurifier.
     *
     * The instance returned is either a newly created instance, or previously created instance
     * that has been cached in a static variable.
     *
     * @param array $args All arguments for the function.
     *                    bool $args['force'] If true, the HTMLPurifier instance will be generated anew, rather than using an
     *                                          existing instance from the static variable.
     *
     * @staticvar array $purifier The HTMLPurifier instance.
     *
     * @return HTMLPurifier The HTMLPurifier instance, returned by reference.
     */
    function &getpurifier($args = null)
    {
        $force = (isset($args['force']) ? $args['force'] : false);

        // prepare htmlpurifier class
        static $purifier;

        if (!isset($purifier) || $force) {
            $config = System::getVar('htmlpurifierConfig');
            if (!is_null($config) && ($config !== false)) {
                $config = unserialize($config);
            } else {
                // Nothing, so set defaults
                $config = array();

                $charset = ZLanguage::getEncoding();
                if (strtolower($charset) != 'utf-8') {
                    // set a different character encoding with iconv
                    $config['Core']['Encoding'] = $charset;
                    // Note that HTML Purifier's support for non-Unicode encodings is crippled by the
                    // fact that any character not supported by that encoding will be silently
                    // dropped, EVEN if it is ampersand escaped.  If you want to work around
                    // this, you are welcome to read docs/enduser-utf8.html in the full package for a fix,
                    // but please be cognizant of the issues the "solution" creates (for this
                    // reason, I do not include the solution in this document).
                }

                // determine doctype of current theme
                // supported doctypes include:
                //
                // HTML 4.01 Strict
                // HTML 4.01 Transitional
                // XHTML 1.0 Strict
                // XHTML 1.0 Transitional (default)
                // XHTML 1.1
                //
                // TODO - we need a new theme field for doctype declaration
                // for now we will use non-strict modes
                $currentThemeID = ThemeUtil::getIDFromName(UserUtil::getTheme());
                $themeInfo = ThemeUtil::getInfo($currentThemeID);
                $useXHTML = (isset($themeInfo['xhtml']) && $themeInfo['xhtml']) ? true : false;

                // as XHTML 1.0 Transitional is the default, we only set HTML (for now)
                if (!$useXHTML) {
                    $config['HTML']['Doctype'] = 'HTML 4.01 Transitional';
                }

                // define where our cache directory lives
                $config['Cache']['SerializerPath'] = CacheUtil::getLocalDir() . '/purifierCache';

                System::setVar('htmlpurifierConfig', serialize($config));
            }

            $purifier = new HTMLPurifier($config);
        }

        return $purifier;
    }

    /**
     * Loads all necessary files for a selected outputfilter and calls it.
     *
     * @param array $args All arguments for the function.
     *                    string $args['var']    The string that should be filtered.
     *                    int    $args['filter'] The filter to use, if not set, we use the configured outputfilter).
     *
     * @throws InvalidArgumentException Thrown if the filter argument is not recognized.
     *
     * @return string The sanitized string if filter is used.
     */
    public function secureoutput($args)
    {
        if (!isset($args['filter']) || empty($args['filter']) || !is_numeric($args['filter'])) {
            $args['filter'] = System::getVar('outputfilter');
        }

        // recursive call for arrays and hashs of vars ;)
        if (is_array($args['var'])) {
            $deep = isset($args['deep']) && is_numeric($args['deep']) ? $args['deep'] : 3;
            if ($deep >= 0) {
                $deep--;
                foreach ($args['var'] as $key => $value) {
                    $args['var'][$key] = $this->secureoutput(
                            array(  'var' => $args['var'][$key],
                            'filter' => $args['filter'],
                            'deep'=>$deep));
                }
            } else {
                return $args['var'];
            }
            return $args['var'];
        } else {
            $returnValue  = $args['var'];
            if ($args['filter'] > 0) {
                // >0 means not the internal filter
                switch($args['filter']) {
                    case 1:
                    // prepare htmlpurifier class
                    //static $purifier, $safecache, $dummy;
                        static $safecache, $dummy;
                        $purifier = &$this->getpurifier();

                        if (!isset($dummy)) {
                            // quick fix for http://noc.postnuke.com/tracker/index.php?func=detail&aid=5662&group_id=5&atid=101
                            // this needs some review as far as the pnRender singleton is concerned
                            $dummy = Renderer::getInstance();
                            // original code:
                            //$dummy = Renderer::getInstance('SecurityCenter');
                        }

                        // calc the key for the value
                        $sha = sha1($returnValue);

                        // check if the value is in the safecache
                        if (isset($safecache[$sha])) {
                            $returnValue = $safecache[$sha];
                        } else {
                            // save pnRender delimiters
                            $returnValue = str_replace($dummy->left_delimiter,  'PNRENDER_LEFT_DELIMITER',  $returnValue);
                            $returnValue = str_replace($dummy->right_delimiter, 'PNRENDER_RIGHT_DELIMITER', $returnValue);
                            $returnValue = $purifier->purify($returnValue);

                            // restore pnRender delimiters
                            $returnValue = str_replace('PNRENDER_LEFT_DELIMITER',  $dummy->left_delimiter,  $returnValue);
                            $returnValue = str_replace('PNRENDER_RIGHT_DELIMITER', $dummy->right_delimiter, $returnValue);

                            // cache the value
                            $safecache[$sha] = $returnValue;
                        }
                        break;
                    case 2:
                    // more outputfilters...
                    default:
                        throw new InvalidArgumentException($this->__('Invalid outputfilter value detected.'));
                }
            }
            return $returnValue;
        }
    }

    /**
     * Check a single value for malicious input.
     *
     * @param int $secValue The input value to check.
     *
     * @return bool True if suspicious content was found, false otherwise.
     */
    private function _checkinputvalue($secValue)
    {
        $result = ((preg_match('/<[^>]*script.*\"?[^>]*>/i', $secValue)) ||
                        (preg_match('/<[^>]*object.*\"?[^>]*>/i', $secValue)) ||
                        (preg_match('/<[^>]*applet.*\"?[^>]*>/i', $secValue)) ||
                        (preg_match('/<[^>]*embed.*\"?[^>]*>/i', $secValue)) ||
                        (preg_match('/<[^>]*form.*\"?[^>]*>/i', $secValue)));

        return $result;
    }

    /**
     * Retrieves configuration array for PHPIDS.
     *
     * @return array IDS configuration settings.
     */
    private function _getidsconfig()
    {
        $config = array();
        $idsDir = $this->baseDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR .'IDS'. DIRECTORY_SEPARATOR;

        // General configuration settings
        $config['General'] = array();

        $config['General']['filter_type'] = System::getVar('idsfilter', 'xml');
        if (empty($config['General']['filter_type'])) {
            $config['General']['filter_type'] = 'xml';
        }

        $config['General']['base_path'] = $idsDir;
        // we don't use the base path because the tmp directory is in zkTemp (see below)
        $config['General']['use_base_path'] = false;

        // path to the filters used
        $config['General']['filter_path'] = $idsDir . 'default_filter.xml';
        // path to (writable) tmp directory
        $config['General']['tmp_path'] = CacheUtil::getLocalDir() . '/idsTmp';
        $config['General']['scan_keys'] = false;

        // we use a different HTML Purifier source
        // by default PHPIDS does also contain those files
        //$config['General']['HTML_Purifier_Path'] = System::getVar('htmlpurifierlocation') . 'HTMLPurifier.auto.php';
        $config['General']['HTML_Purifier_Cache'] = CacheUtil::getLocalDir() . '/purifierCache';

        // define which fields contain html and need preparation before hitting the PHPIDS rules
        $config['General']['html'] = System::getVar('idshtmlfields', array());

        // define which fields contain JSON data and should be treated as such for fewer false positives
        $config['General']['json'] = System::getVar('idsjsonfields', array());;

        // define which fields shouldn't be monitored (a[b]=c should be referenced via a.b)
        $config['General']['exceptions'] = System::getVar('idsexceptions', array());;

        // PHPIDS should run with PHP 5.1.2 but this is untested - set this value to force compatibilty with minor versions
        $config['General']['min_php_version'] = '5.1.6';


        // caching settings
        // @todo: add UI for those caching settings
        $config['Caching'] = array();

        // caching method (session|file|database|memcached|none), default file
        $config['Caching']['caching'] = 'none'; // deactivate caching for now
        $config['Caching']['expiration_time'] = 600;

        // file cache
        $config['Caching']['path'] = $config['General']['tmp_path'] . '/default_filter.cache';

        // database cache
        //$config['Caching']['wrapper'] = 'mysql:host=localhost;port=3306;dbname=phpids';
        //$config['Caching']['user'] = 'phpids_user';
        //$config['Caching']['password'] = '123456';
        //$config['Caching']['table'] = 'cache';

        // memcached
        //$config['Caching']['host'] = 'localhost';
        //$config['Caching']['port'] = 11211;
        //$config['Caching']['key_prefix'] = 'PHPIDS';
        //$config['Caching']['tmp_path'] = $config['General']['tmp_path'] . '/memcache.timestamp';

        return $config;
    }

    /**
     * Process results from IDS scan.
     *
     * @param IDS_Init   $init   PHPIDS init object reference.
     * @param IDS_Report $result The result object from PHPIDS.
     *
     * @return void
     */
    private function _processIdsResult(IDS_Init $init, IDS_Report $result)
    {
        // $result contains any suspicious fields enriched with additional info

        // Note: it is moreover possible to dump this information by simply doing
        //"echo $result", calling the IDS_Report::$this->__toString() method implicitely.

        $requestImpact = $result->getImpact();
        if ($requestImpact < 1) {
            // nothing to do
            return;
        }

        // update total session impact to track an attackers activity for some time
        $sessionImpact = SessionUtil::getVar('idsImpact', 0) + $requestImpact;
        SessionUtil::setVar('idsImpact', $sessionImpact);

        // let's see which impact mode we are using
        $idsImpactMode = System::getVar('idsimpactmode', 1);
        $idsImpactFactor = 1;
        if ($idsImpactMode == 1) {
            $idsImpactFactor = 1;
        } elseif ($idsImpactMode == 2) {
            $idsImpactFactor = 10;
        } elseif ($idsImpactMode == 3) {
            $idsImpactFactor = 5;
        }

        // determine our impact threshold values
        $impactThresholdOne   = System::getVar('idsimpactthresholdone',    1) * $idsImpactFactor;
        $impactThresholdTwo   = System::getVar('idsimpactthresholdtwo',   10) * $idsImpactFactor;
        $impactThresholdThree = System::getVar('idsimpactthresholdthree', 25) * $idsImpactFactor;
        $impactThresholdFour  = System::getVar('idsimpactthresholdfour',  75) * $idsImpactFactor;

        $usedImpact = ($idsImpactMode == 1) ? $requestImpact : $sessionImpact;

        // react according to given impact
        if ($usedImpact > $impactThresholdOne) {
            // db logging

            // determine IP address of current user
            $_REMOTE_ADDR = System::serverGetVar('REMOTE_ADDR');
            $_HTTP_X_FORWARDED_FOR = System::serverGetVar('HTTP_X_FORWARDED_FOR');
            $ipAddress = ($_HTTP_X_FORWARDED_FOR) ? $_HTTP_X_FORWARDED_FOR : $_REMOTE_ADDR;

            $currentPage = System::getCurrentUri();
            $currentUid = UserUtil::getVar('uid');

            // log details to database
            foreach ($result as $event) {
                $eventName = $event->getName();
                $malVarName = substr($eventName, 2);

                $newIntrusionItem = array(
                        'name'    => $eventName,
                        'tag'     => $malVarName,
                        'value'   => $event->getValue(),
                        'page'    => $currentPage,
                        'uid'     => $currentUid,
                        'ip'      => $ipAddress,
                        'impact'  => $result->getImpact(),
                        'date'    => DateUtil::getDatetime()
                );

                //$eventType = substr($eventName, 1, 0);

                // **
                // debug stuff
                // **
                //$eventFilters = '';
                //foreach($event as $filter) {
                //    $eventFilters .= $this->__fGT(_('Rule: %s'), array($filter->getRule())) . "\n<br />";
                //    $eventFilters .= $this->__fGT(_('Description: %s'), array($filter->getDescription())) . "\n<br />";
                //    $eventFilters .= $this->__fGT(_('Impact: %s'), array($filter->getImpact())) . "\n<br />\n<br />";
                //}
                //LogUtil::registerStatus($eventName . ' (' . DataUtil::formatForDisplay($event->getValue()) . "\n<br />\n<br />" . $eventFilters);

                // create new ZIntrusion instance
                $obj = new SecurityCenter_DBObject_Intrusion();
                // set data
                $obj->setData($newIntrusionItem);
                // save object to db
                $obj->save();
            }
        }

        if ($usedImpact > $impactThresholdTwo) {
            // mail admin

            // prepare mail text
            $mailBody = $this->__('The following attack has been detected by PHPIDS') . "\n\n";
            $mailBody .= $this->__f('IP: %s', $ipAddress) . "\n";
            $mailBody .= $this->__f('UserID: %s', $currentUid) . "\n";
            $mailBody .= $this->__f('Date: %s', DateUtil::strftime( $this->__('%b %d, %Y'), (time()))) . "\n";
            if ($idsImpactMode == 1) {
                $mailBody .= $this->__f('Request Impact: %d', $requestImpact) . "\n";
            } else {
                $mailBody .= $this->__f('Session Impact: %d', $sessionImpact) . "\n";
            }
            $mailBody .= $this->__f('Affected tags: %s', join(' ', $result->getTags())) . "\n";

            $attackedParameters = '';
            foreach ($result as $event) {
                $attackedParameters .= $event->getName() . '=' . urlencode($event->getValue()) . ", ";
            }

            $mailBody .= $this->__f('Affected parameters: %s', trim($attackedParameters)) . "\n";
            $mailBody .= $this->__f('Request URI: %s', urlencode($currentPage));

            // prepare other mail arguments
            $siteName = System::getVar('sitename');
            $adminmail = System::getVar('adminmail');
            $mailTitle = $this->__('Intrusion attempt detected by PHPIDS');

            if (ModUtil::available('Mailer')) {
                $args = array();
                $args['fromname']    = $siteName;
                $args['fromaddress'] = $adminmail;
                $args['toname']      = 'Site Administrator';
                $args['toaddress']   = $adminmail;
                $args['subject']     = $mailTitle;
                $args['body']        = $mailBody;

                $rc = ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $args);
            } else {
                $headers = "From: $siteName <$adminmail>\n"
                        ."X-Priority: 1 (Highest)";
                System::mail($adminmail, $mailTitle, $mailBody, $headers);
            }
        }

        if ($usedImpact > $impactThresholdThree) {
            // block request

            if (System::getVar('idssoftblock')) {
                // warn only for debugging the ruleset
                LogUtil::registerError(__('Malicious request code / a hacking attempt was detected. This request has NOT been blocked!'));
            } else {
                $this->throwForbidden(__('Malicious request code / a hacking attempt was detected. Thus this request has been blocked.'), null, $result);
            }
        }

        return;
    }
}
