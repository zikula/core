<?php
/**
 * The Zikula Recovery Console
 * ------------------------------------------
 * A warehouse of utilities that are designed
 * to fix many common issues a Zikula website
 * might face. Designed for Zikula 1.3.0 only.
 * ------------------------------------------
 * Developed by John Alarcon
 * Copyright 2007-2008
 * http://www.johnalarcon.com
 * ------------------------------------------
 * Licensed under the General Public License.
 */

// Instantiate a Zikula Recovery Console object.
$zrc = new RecoveryConsole();

// Render the object.
$zrc->renderRecoveryConsole();

/**
 * Recovery Console class.
 */
class RecoveryConsole
{
    protected $blocks         = array();
    protected $themes         = array();
    protected $modules        = array();
    protected $siteInactive   = false;

    // Setup.
    public function __construct()
    {
        // Set strict error reporting.
        error_reporting(2047);
        // Initialize Recovery Console config and texts.
        $this->initRecoveryConsole();
        // Initialize Zikula.
        $this->initZikula();
        // Initialize lockdown mechanism.
        $this->initLockMechanism();
        // Sets cleaned user input to object.
        $this->cleanUserInput();
        // load basic data from site
        $this->loadInformation();
    }
    // Initialize the recovery console config and texts.
    public function initRecoveryConsole()
    {
        // Define config settings; will be used in text defines to some degree.
        $this->initAppConfigDefines();
        // Define all Recovery Console texts.
        $this->initAppLangDefines();
    }
    // Define Recovery Console's main config settings.
    public function initAppConfigDefines()
    {
        // Main configuration settings.
        define('_ZRC_APP_TITLE',     'Zikula Recovery Console');
        define('_ZRC_APP_VERSION',   '1.3.0');
        define('_ZRC_APP_SCRIPT',    basename($_SERVER['PHP_SELF']));
        define('_ZRC_APP_EXPIRES',   1200);

        // Set config file target to object early on, for usage in text defines.
        $dir = explode('/', $_SERVER['PHP_SELF']);
        $this->siteConfigFile = $this->getServerProtocol().'://'.$this->getHost().'/'.$dir[1].'/config/config.php';
    }

    // load all information needed
    public function loadInformation()
    {
        // Check for failed database connection.
        if (!$this->dbEnabled) {
            return false;
        }

        //
        // load all blocks.
        //
        $this->blocks = ModUtil::apiFunc('Blocks', 'user', 'getall', array('inactive'=>true));

        //
        // load all themes
        //
        $filethemes = array();
        // Open the themes directory.
        $handle = opendir('themes');
        // Read the directory contents.
        while ($dir = readdir($handle)) {
            // Skip any files, up-dirs, and certain themes.
            if (substr($dir, 0, 1) == '.' ||
                substr($dir, 0, 5)=='index' ||
                substr($dir, 0, 3)=='rss' ||
                substr($dir, 0, 4)=='Atom' ||
                substr($dir, 0, 7)=='Printer' ||
                substr($dir, 0, 9)=='AutoPrint') {
                continue;
            }
            // Catch the theme name.
            $filethemes[] = $dir;
        }
        // Close the directory.
        closedir($handle);

        // Loop through each theme.
        foreach ($filethemes as $theme) {
            // check if theme is an autotheme
            if (file_exists('themes/'.$theme.'/theme.cfg')) {
                $this->themes['autotheme'][$theme] = array('name' => $theme, 'state' => 1);
            } else {
                $themeid = ThemeUtil::getIDFromName($theme);
                if ($themeid <> false) {
                    $this->themes['corethemes'][$theme] = ThemeUtil::getInfo(ThemeUtil::getIDFromName($theme));
                } else {
                    $this->themes['corethemes'][$theme] = array('name' => $theme, 'state' => 0);
                }
            }
        }
        ksort($this->themes['corethemes']);
        ksort($this->themes['autotheme']);

        //
        // get site status
        //
        $this->siteInactive = (System::getVar('siteoff') == 1);

        //
        // load all modules.
        //
        $mods = DBUtil::selectObjectArray('modules','pn_id>0','type');
        // Loop through modules, sorting.
        foreach ($mods as $mod) {
            if ($mod['type'] == 3) {
                $this->modules['sys_mods'][$mod['name']] = $mod;
            } else if ($mod['type'] == 2) {
                $this->modules['usr_mods'][$mod['name']] = $mod;
            }
        }
        ksort($this->modules['sys_mods']);
        ksort($this->modules['usr_mods']);

        return true;
    }

    // Texts for the Recovery Console.
    public function initAppLangDefines()
    {
        // Navigational texts.
        define('_ZRC_TXT_NAV_MAIN_SHORT',                'Main');
        define('_ZRC_TXT_NAV_MAIN_LONG',                 'Configuration Overview');
        define('_ZRC_TXT_NAV_THEME',                     'Theme Recovery');
        define('_ZRC_TXT_NAV_PERMISSION',                'Permission Recovery');
        define('_ZRC_TXT_NAV_DISABLEDSITE',              'Disabled Site Recovery');
        define('_ZRC_TXT_NAV_BLOCK',                     'Block Recovery');
        define('_ZRC_TXT_NAV_PASSWORD',                  'Password Reset');
        define('_ZRC_TXT_NAV_ABOUT',                     'About This Application');
        define('_ZRC_TXT_NAV_PHPINFO',                   'PHP Information');
        define('_ZRC_TXT_NAV_PHPINFO_VERSION',           'PHP Version');
        define('_ZRC_TXT_NAV_PHPINFO_CORE',              'PHP Core');
        define('_ZRC_TXT_NAV_PHPINFO_APACHE',            'Apache Environment');
        define('_ZRC_TXT_NAV_PHPINFO_ENVIRONMENT',       'PHP Environment');
        define('_ZRC_TXT_NAV_PHPINFO_VARIABLES',         'PHP Variables');
        define('_ZRC_TXT_NAV_PHPINFO_LICENSE',           'PHP License');
        // General texts.
        define('_ZRC_TXT_CLICK_TO_CONFIRM',              'Click To Confirm');
        define('_ZRC_TXT_RUN_UTILITY',                   'Run Utility');
        define('_ZRC_TXT_ERROR',                         'ERROR');
        define('_ZRC_TXT_STATUS',                        'STATUS');
        define('_ZRC_TXT_LOCKDOWN_TIMER',                'LOCKDOWN TIMER');
        define('_ZRC_TXT_LOCKDOWN_ENGAGED',              'LOCKDOWN ENGAGED');
        define('_ZRC_TXT_LOCKDOWN_REASON',               'The '._ZRC_APP_TITLE.' will be automatically disabled when the timer expires.  If it expires before you finish your work, simply re-upload the file and refresh your browser.  The timer will be reset.');
        define('_ZRC_TXT_LOCKDOWN_ACTIVE',               'The '._ZRC_APP_TITLE.' is now disabled from further use as a security precaution.  You must upload a new copy of this file to reset the lockdown timer.');
        define('_ZRC_TXT_RECOVERY_SUCCESS',              'Recovery Successful');
        define('_ZRC_TXT_UTILITY_DISABLED',              'This utility is now disabled.');
        define('_ZRC_TXT_UTILITY_DISABLED_BLOCK',        'There are no blocks existing on your site which means that this utility can provide no assistance.');
        define('_ZRC_TXT_UTILITY_DISABLED_SITE',         'Your site is already enabled.');
        define('_ZRC_TXT_STILL_NEED_HELP',               'Still need help?');
        define('_ZRC_TXT_SEARCH_THE_DOTCOM',             'Search the <em>entire</em> Zikula site here!');
        define('_ZRC_TXT_SEARCH_BTN',                    'SEARCH');
        define('_ZRC_TXT_INSTRUCTIONS',                  'INSTRUCTIONS');
        define('_ZRC_TXT_NOT_APPLICABLE_ABBR',           'N/A');
        // Overview texts.
        define('_ZRC_TXT_OVERVIEW_VERSION',              'Core Version');
        define('_ZRC_TXT_OVERVIEW_DATABASE',             'Database');
        define('_ZRC_TXT_OVERVIEW_THEME',                'Current Theme');
        define('_ZRC_TXT_OVERVIEW_LANGUAGE',             'Site Language');
        define('_ZRC_TXT_OVERVIEW_STATUS',               'Site Status');
        define('_ZRC_TXT_OVERVIEW_SITE_ON',              'On/Enabled');
        define('_ZRC_TXT_OVERVIEW_SITE_OFF',             'Off/Disabled');
        define('_ZRC_TXT_OVERVIEW_CONNECTED',            'Connected');
        define('_ZRC_TXT_OVERVIEW_NOT_CONNECTED',        'Not Connected');
        define('_ZRC_TXT_OVERVIEW_CONFIG',               'Config File');
        define('_ZRC_TXT_OVERVIEW_CORETHEMES',           'Core Themes');
        define('_ZRC_TXT_OVERVIEW_AUTOTHEMES',           'AutoThemes');
        define('_ZRC_TXT_OVERVIEW_DETECTED_MODS',        'Detected Modules');
        define('_ZRC_TXT_OVERVIEW_DETECTED_BLOCKS',      'Detected Blocks');
        define('_ZRC_TXT_OVERVIEW_DETECTED_THEMES',      'Detected Themes');
        define('_ZRC_TXT_OVERVIEW_SYSTEM_MODS',          'System Modules');
        define('_ZRC_TXT_OVERVIEW_3RDPARTY_MODS',        'Value Added Modules');
        define('_ZRC_TXT_OVERVIEW_ACTIVE',               'active');
        define('_ZRC_TXT_OVERVIEW_INACTIVE',             'inactive');
        define('_ZRC_TXT_OVERVIEW_UNINITIALIZED',        'uninitialized');
        define('_ZRC_TXT_OVERVIEW_INVALID',              'invalid');
        define('_ZRC_TXT_OVERVIEW_FILESMISSING',         'files missing');
        define('_ZRC_TXT_OVERVIEW_UPGRADED',             'upgraded');
        define('_ZRC_TXT_OVERVIEW_DEFAULT_THEME',        'set as default');
        // Texts for current settings.
        define('_ZRC_TXT_CURRENT_SETTING',               'Current Setting:');
        define('_ZRC_TXT_SITE_DISABLED',                 'Site Is Off/Disabled');
        define('_ZRC_TXT_SITE_ENABLED',                  'Site Is On/Enabled');
        define('_ZRC_TXT_NOTHING_TO_REPORT',             'Nothing To Report');
        define('_ZRC_TXT_BLOCKS_NOT_FOUND',              'No Blocks Were Found');
        define('_ZRC_TXT_BLOCKS_OUTLINED_BELOW',         'Blocks Are Outlined Below');
        define('_ZRC_TXT_NONE_DETECTED',                 'None Detected');
        // Texts specific to site recovery.
        define('_ZRC_TXT_SITE_TURNITON',                 'Set the following checkbox to re-enable your site.');
        // Texts specific to block recovery.
        define('_ZRC_TXT_BLOCK_BID',                     'BID');
        define('_ZRC_TXT_BLOCK_MID',                     'MID');
        define('_ZRC_TXT_BLOCK_KEY',                     'Key');
        define('_ZRC_TXT_BLOCK_TITLE',                   'Title');
        define('_ZRC_TXT_BLOCK_STATE',                   'State');
        define('_ZRC_TXT_BLOCK_ACTIVE',                  'active');
        define('_ZRC_TXT_BLOCK_INACTIVE',                'inactive');
        define('_ZRC_TXT_BLOCK_ACTIONS',                 'ACTIONS');
        define('_ZRC_TXT_BLOCK_NOCHANGE',                'Make No Changes');
        define('_ZRC_TXT_BLOCK_DEACTIVE',                'Disable Block');
        define('_ZRC_TXT_BLOCK_DELETE',                  'Delete Block');
        define('_ZRC_TXT_BLOCK_DISABLE_FAILED',          'Block Not Disabled');
        define('_ZRC_TXT_BLOCK_DISABLED',                'Block Disabled');
        define('_ZRC_TXT_BLOCK_DELETE_FAILED',           'Block Not Deleted');
        define('_ZRC_TXT_BLOCK_DELETED',                 'Block Deleted');
        define('_ZRC_TXT_BLOCK_NO_BLOCKS_EXIST',         'This utility is disabled.  There are no existing blocks for your site, so this utility can provide no assistance.');
        // Texts specific to permission recovery.
        define('_ZRC_TXT_PERMISSION_EXAMPLE',            'Default Permissions');
        // Texts specific to theme recovery.
        define('_ZRC_TXT_THEME_AVAILABLE',               'Available Themes');
        define('_ZRC_TXT_THEME_RESET_USERS',             'Reset User Themes');
        define('_ZRC_TXT_THEME_CORETHEMES',              'Core Themes');
        define('_ZRC_TXT_THEME_AUTOTHEMES',              'AutoThemes');
        // Texts specific to password reset.
        define('_ZRC_TXT_PASSWORD_UNAME',                'Username');
        define('_ZRC_TXT_PASSWORD_UPASS',                'New password');
        define('_ZRC_TXT_PASSWORD_UPASSAGAIN',           'New password again (for verification)');
        define('_ZRC_TXT_PASSWORD_RESETSUCCESS',         'The password was successfully reset');
        // Explanatory texts.
        define('_ZRC_EXP_MAIN',                          'The information shown below reflects the current configuration settings detected by the '._ZRC_APP_TITLE.'. Using the navigation at left, make use of the various site recovery utilities available. If the '._ZRC_APP_TITLE.' cannot resolve the issues your site is experiencing, try the search box at the bottom of any page to search the <a href="http://community.zikula.org/index.php?module=Forum" title="Zikula Support Forum">Zikula Support Forum</a> for answers.');
        define('_ZRC_EXP_THEME',                         '<strong>'._ZRC_TXT_INSTRUCTIONS.':</strong> Use this utility to recover from theme-related fatal errors or to reset user-specified themes. Note that no AutoThemes will be available in the drop-down menu unless you have the AutoTheme module installed and active.');
        define('_ZRC_EXP_PERMISSION',                    '<strong>'._ZRC_TXT_INSTRUCTIONS.':</strong> Use this utility to reset your site permissions to the default state that was set when you originally installed the site.  Carefully review the chart below as an example of how your permissions will be setup after running this utility.');
        define('_ZRC_EXP_BLOCK',                         '<strong>'._ZRC_TXT_INSTRUCTIONS.':</strong> Use this utility to disable or delete blocks that you believe are causing issues for your site. Blocks that you disable can still be accessed by the system, but a deleted block is gone for good; double-check your choices before running this utility.  If there are no blocks present on your site, this utility will be disabled.');
        define('_ZRC_EXP_SITE',                          '<strong>'._ZRC_TXT_INSTRUCTIONS.':</strong> Use this utility to turn on your previously disabled site.');
        define('_ZRC_EXP_PASSWORD',                      '<strong>'._ZRC_TXT_INSTRUCTIONS.':</strong> Use this utility to reset your admin (or other) password.');
        // About page texts.
        define('_ZRC_EXP_ABOUT_TXT_VERSION',             '<strong>VERSION</strong><br />This is <strong>Version '._ZRC_APP_VERSION.'</strong> of the <strong>'._ZRC_APP_TITLE.'</strong><br /><br />');
        define('_ZRC_EXP_ABOUT_TXT_INFO',                '<strong>GENERAL</strong><br />The '._ZRC_APP_TITLE.' provides the tools necessary to resolve and recover from the most common issues that a Zikula site might experience over its lifetime.  Contained within this application are a variety of powerful recovery utilities that are designed to be self-explanatory, simple to use, and consistently delivered through an aesthetic layout.<br /><br />');
        define('_ZRC_EXP_ABOUT_TXT_LICENSE',             '<strong>LICENSE</strong><br /><a href="http://www.gnu.org/copyleft/gpl.html" title="General Public License">General Public License</a><br /><br />');
        define('_ZRC_EXP_ABOUT_TXT_CREDITS',             '<strong>CREDITS</strong><br />Maintained and enhanced by the Zikula CoreDev team. Originally developed by <a href="http://www.alarconcepts.com/" title="John Alarcon">John Alarcon</a>.  Greatly inspired by the ideas and work of <a href="http://www.snowjournal.com" title="Christopher S. Bradford">Christopher S. Bradford</a> and the additional supportive efforts of <a href="http://users.tpg.com.au/staer/" title="Martin Andersen">Martin Andersen</a>, <a href="http://www.landseer-stuttgart.de/" title="Frank Schummertz">Frank Schummertz</a>, <a href="http://pahlscomputers.com/" title="David Pahl">David Pahl</a> and <a href="http://www.itbegins.co.uk/" title="Simon Birtwistle">Simon Birtwistle</a>. Thanks guys!');
        // Error texts.
        define('_ZRC_ERR_WRONG_DIRECTORY',               '<strong>THIS APPLICTATION WAS IMPROPERLY UPLOADED</strong><br />Please ensure that you have uploaded this file to<br />the <em>root directory</em> of your site and try again.');
        define('_ZRC_ERR_CMS_FAILED',                    '<strong>Zikula COULD NOT BE INITIALIZED</strong><br />No further information is available.');
        define('_ZRC_ERR_INCOMPATIBLE',                  '<strong>THIS APPLICATION IS INCOMPATIBLE WITH YOUR SITE<br />This application works only with Zikula 1.x+.<br />When stil using PostNuke .764 consider the <a href="http://community.zikula.org/Downloads-req-viewdownload-cid-7.htm" title="PostNuke Swiss Army Knife">PostNuke Swiss Army Knife</a> (PSAK) utility.');
        define('_ZRC_ERR_APP_LOCKED',                    '<strong>THIS APPLICATION HAS EXPIRED</strong><br />Re-upload the file to reset the timer.');
        define('_ZRC_ERR_CONFIRM_REQUIRED',              'Confirmation Required');
        define('_ZRC_ERR_DUPED_SETTING',                 'This particular aspect of your site does not appear to be broken in its current state.  No changes were made.');
        define('_ZRC_ERR_FORM_INCOMPLETE',               'All Fields Required');
        define('_ZRC_ERR_PASSWORD_EMPTYUNAME',           'Username cannot be empty');
        define('_ZRC_ERR_PASSWORD_EMPTYPASS',            'Password cannot be empty');
        define('_ZRC_ERR_PASSWORD_MISMATCH',             'Passwords do not match');
        define('_ZRC_ERR_PASSWORD_INVALIDUSERNAME',      'The username you supplied is not valid');
        define('_ZRC_ERR_PASSWORD_INVALIDPASSWORD',      'The password you supplied is not valid');
        define('_ZRC_ERR_PASSWORD_ANONYMOUSUSERNAME',    'You cannot change the password for the anonymous user. Please provide the username of a valid user');
        define('_ZRC_ERR_PASSWORD_RESETFAILED',          'Error resetting the password');
        define('_ZRC_ERR_RECOVERY_FAILURE',              'Recovery Failed');
        define('_ZRC_ERR_INVALID_UTILITY',               'Invalid Utility');
        define('_ZRC_ERR_INVALID_OPERATION',             'Invalid Operation');
        define('_ZRC_ERR_ALL_FIELDS_REQUIRED',           'All Fields Required');
        define('_ZRC_ERR_THEME_INVALID',                 'Invalid Theme Name');
        define('_ZRC_ERR_PERMISSION_DEFAULTING',         'Error Defaulting Permissions');
        define('_ZRC_ERR_PERMISSION_INSERT_FAILURE',     'Error Inserting Default Permissions');
        define('_ZRC_ERR_PERMISSIONS_NOT_DELETED',       'Error Deleting Current Permissions');
        define('_ZRC_ERR_SITE_ENABLING',                 'Error Enabling Site');
        define('_ZRC_ERR_BLOCK_INVALID_BID',             'Block ID Invalid');
        define('_ZRC_ERR_BLOCK_NO_SUCH_BLOCK',           'No Such Block Exists');
        define('_ZRC_ERR_BLOCK_NOT_DISABLED',            'Block Not Disabled');
        define('_ZRC_ERR_BLOCK_NOT_DISPLACED',           'Block Placements Not Removed');
        define('_ZRC_ERR_BLOCK_NOT_DELETED',             'Block Not Deleted');
        define('_ZRC_ERR_CAT_MOVE_FAILURE',              'Failed To Move Category');
        define('_ZRC_ERR_NO_DATA_NO_CHANGE',             'No Changes Were Made');
        define('_ZRC_ERR_RESETTING_USER_THEMES',         'Error Resetting User Themes');
    }
    // Initialize Zikula and set relevant properties.
    public function initZikula()
    {
        // Assign path/System file.
        $file = 'lib/ZLoader.php';
        // Before inclusion, ensure the API file can be accessed.
        if (!file_exists($file) && !is_readable($file)) {
            $this->fatalError(_ZRC_ERR_WRONG_DIRECTORY);
        }
        // Include the API file.
        require_once 'lib/ZLoader.php';
        ZLoader::register();
        // Before init, avoid error; ensure the function exists.
        if (!is_callable(array('System', 'init'))) {
            $this->fatalError(_ZRC_ERR_CMS_FAILED);
        }
        // Initialize Zikula.

        System::init();
        // Setting various site properties.
        $this->dbEnabled        = $this->initDatabase();
        $this->siteLang         = ZLanguage::getLanguageCode();
        $this->siteVersion      = System::VERSION_NUM;
        $this->siteCodebase     = System::VERSION_ID;
        $this->siteInactive     = System::getVar('siteoff');
        $this->siteTheme        = System::getVar('Default_Theme');
        $this->operation        = null;
        $this->utility          = null;
    }
    // Check if lockdown is engaged.
    public function initLockMechanism()
    {
        // Kill app if app is expired or force-locked by the system.
        if ($this->appIsExpired() || $this->appForceLocked) {
            //$this->fatalError(_ZRC_ERR_APP_LOCKED);
        }
    }
    // Database connection.
    public function initDatabase()
    {
        // Establish database connection or return false.
        if (!$this->dbConnection = DBConnectionStack::getConnection()) {
            return false;
        }
        // Get Zikula table data or return false.
        if (!$this->dbTables = System::dbGetTables()) {
            return false;
        }
        // Return success.
        return true;
    }
    // Clean any input and set to object.
    public function cleanUserInput($input=false)
    {
        // Ensure input exists.
        if (!$input) {
            $input = $_REQUEST;
            unset($_REQUEST);
        }
        // Cleaning single values returns here.
        if (!is_array($input)) {
            return (string)$cleaned = preg_replace('~\W+~', '', $input);
        }
        // Else dealing with an array.
        // Looping through array values.
        foreach ($input as $key=>$val) {
            // Special case: dba user/pass can contain more than text chars, do it inline.
            if ($key=='dbuname' || $key=='dbpass1' || $key=='dbpass2') {
                $cleaned[$key] = preg_replace('[!@#$%^_\w]', '', $val);
            } else {
                // If $val is not an array, clean and add it to the $cleaned array.
                if (!is_array($val)) {
                    $cleaned[$key] = $this->cleanUserInput($val);
                } else {
                    // Else loop through $val, clean and add its data to $cleaned array.
                    foreach($val as $key2=>$val2) {
                        $cleaned[$key][$key2] = $this->cleanUserInput($val2);
                    }
                }
            }
        }
        // Check if operation is valid.
        if (!empty($cleaned['op']) && in_array($cleaned['op'], $this->getAllOperations())) {
            // If valid, reset property.
            $this->operation = $cleaned['op'];
        } else {
            // If invalid, remove op and utility from cleaned data.
            $cleaned['op'] = $cleaned['utility'] = null;
        }
        // Check if utility is valid.
        if (!empty($cleaned['utility']) && in_array($cleaned['utility'], $this->getAllUtilities())) {
            // If valid, reset property.
            $this->utility = $cleaned['utility'];
        } else {
            // If invalid, remove utility from cleaned data.
            $cleaned['utility'] = null;
        }
        // Finally, set the rest of the cleaned input to object.
        $this->INPUT = $cleaned;
        // Return.
        return;
    }

    // Check if Recovery Console is compatible with site version.
    public function appCompatible()
    {
        // Return false if site version is not from Zikula 1.x series.
        return ((int)substr(System::VERSION_NUM, 0, 1) == 1);
    }

    // Check if application is expired, thus warranting lockdown.
    public function appIsExpired()
    {
        // Check if app is expired.
        if (time() > $this->appExpirationTime()) {
            return true;
        }
        // App is not expired.
        return false;
    }
    // Return the unix time this file was created.
    public function appCreationTime()
    {
        return filemtime(_ZRC_APP_SCRIPT);
    }
    // Return the unix time that this file should lockdown.
    public function appExpirationTime()
    {
        return $this->appCreationTime() + _ZRC_APP_EXPIRES;
    }
    // Return the seconds left until file lockdown.
    public function appTimeElapser()
    {
        return $this->appExpirationTime() - time();
    }

    // Markup the Recovery Console page header.
    public function markupHeader()
    {
        // Handles everything through the opening <body> tag, inclusive.
        $head  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'."\n";
        $head .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
        $head .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en_US">'."\n\n";
        $head .= '<head>'."\n\n";
        $head .= '<meta http-equiv="content-type" content="text/html; charset=utf-8" />'."\n\n";
        $head .= '<title>'._ZRC_APP_TITLE.'</title>'."\n\n";
        // Countdown timer script.  For GUI purposes only; security is not dependent upon Javascript.
        $head .= $this->markupJavascript();
        // All CSS comes via this method.
        $head .= $this->markupStyles();
        $head .= '</head>'."\n\n";
        $head .= '<body>'."\n\n";
        $head .= '<div id="container">'."\n\n";
        $head .= '    <div id="app_title">'._ZRC_APP_TITLE.'</div>'."\n\n";
        // Return the string.
        return $head;
    }
    // Markup the Recovery Console page script.
    public function markupJavascript()
    {
        $js  = '<script type="text/javascript">'."\n";
        $js .= '<!--'."\n";
        $js .= '    public function countDown(n) {'."\n";
        $js .= '        if(document.getElementById("timer_container")) {';
        $js .= '            mins = Math.floor(n/60);'."\n";
        $js .= '            if(mins < 10) {'."\n";
        $js .= '                mins = "0" + mins;'."\n";
        $js .= '            }'."\n";
        $js .= '            secs = n - mins*60;'."\n";
        $js .= '            if(secs < 10) {'."\n";
        $js .= '                secs = "0" + secs;'."\n";
        $js .= '            }'."\n";
        $js .= '            if(n > 0) {'."\n";
        $js .= '                document.getElementById("timer_container").innerHTML = \''._ZRC_TXT_LOCKDOWN_TIMER.'<div id="timer_digits">\'+mins+\':\' + secs + \'<\/div><div id="timer_reason">'._ZRC_TXT_LOCKDOWN_REASON.'<\/div>\';'."\n";
        $js .= '                setTimeout(function() { countDown(n - 1) }, 1000)'."\n";
        $js .= '            } else if (n == 0) {'."\n";
        $js .= '                document.getElementById("timer_container").innerHTML =  \''._ZRC_TXT_LOCKDOWN_ENGAGED.'<div id="timer_reason" class="red">'._ZRC_TXT_LOCKDOWN_ACTIVE.'<\/div>\';'."\n";
        $js .= '            }'."\n";
        $js .= '        }'."\n";
        $js .= '    }'."\n";
        $js .= '    onload = function() { countDown('.$this->appTimeElapser().') }'."\n";
        $js .= '-->'."\n";
        $js .= '</script>'."\n\n";
        // Return created string.
        return $js;
    }
    // Markup the Recovery Console page CSS.
    public function markupStyles()
    {
        // Markup the styles as a long HTML string.
        $css = '<style type="text/css">
    /* --- BASE CLASSES --- */
    body {
        margin: 0;
        padding: 0;
        background: #1e74cb;
        font: normal 10pt verdana;
        color: #000;
        }
    td,
    th {
        vertical-align: top;
        text-align: left;
        font-size: 8pt;
        }
    ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        }
    li {
        line-height: 1.5em;
        }
    label,
    input,
    select {
        margin: 0 0 10px 0;
        font-size: 11pt;
        }
    optgroup {
        color: #000;
        }
    option {
        color: #1e74cb;
        }
    pre {
        padding: 10px;
        border: 1px solid #00f;
        background: #ffffd8;
        font-family: monospace;
        font-size: 9pt;
        color: #00f;
        }
    img {
        border: 0;
        }
    a:link,
    a:visited {
        text-decoration: none;
        color: #00f;
        }
    a:hover {
        text-decoration: underline;
        color: #f00;
        }
    del {
        color: #ccc;
        }
    #container,
    .bar {
        background: #eee;
        }
    #app_title {
        padding: 5px;
        border-bottom: 1px solid #000;
        background: #1e74cb;
        font-size: 16pt;
        font-weight: bold;
        font-style: italic;
        color: #fff;
        }
    #left_column {
        width: 25%;
        display: inline;
        float: left;
        }
    #navblock {
        margin: 15px 0 0 15px;
        border: 1px solid #000;
        border-bottom: 0;
        background: #fff;
        text-align: left;
        font-size: 10pt;
        }
    #navblock_asterisk {
        margin: 0 0 10px 15px;
        text-align: right;
        font-size: 8pt;
        }
    #navblock a:link,
    #navblock a:visited {
        display: block;
        padding: 5px;
        border-bottom: 1px solid #000;
        text-decoration: none;
        color: #1e74cb;
        }
    #navblock a:hover {
        border-right: 5px solid #c1c8cf;
        border-bottom: 1px solid #000;
        background: #f2f2f2;
        color: #1e74cb;
        }
    #navblock a.selected:link,
    #navblock a.selected:visited {
        border-right: 5px solid #1e74cb;
        background: #85b7ea;
        font-weight: bold;
        color: #fff;
        }
    #navblock a.selected:hover {
        border-right: 5px solid #1e74cb;
        }
    #timer_container {
        margin: 20px 0 20px 15px;
        padding: 10px;
        border: 1px solid #f00;
        background: #ffffd8;
        text-align: center;
        font-size: 12pt;
        font-weight: bold;
        color: #f00;
        }
    #timer_digits {
        font-family: monospace;
        font-size: 24pt;
        }
    #timer_reason {
        text-align: justify;
        font-size: 8pt;
        font-weight: normal;
        }
    #status,
    #error {
        margin: 0 0 10px 0;
        padding: 10px;
        border: 1px solid #000;
        background: #fff;
        }
    #error {
        background: #ffffd8;
        }
    #fatal {
        width: 100%;
        text-align: center;
        margin: 100px 0 200px 0;
        font-size: 10pt;
        color: #f00;
    }
    #body_column {
        display: inline;
        float: left;
        width: 55%;
        margin: 15px;
        padding: 10px;
        border: 1px solid #000;
        background: #fff;
        }
    #page_title {
        padding: 0 0 10px 0;
        font-size: 20pt;
        color: #1e74cb;
        font-family: tahoma, sans-serif;
        }
    /* GENERAL RULES FOR EXPLANATIONS, CURRENT SETTINGS AND INFORMATIONS */
    #explain,
    #explain_disabled,
    #current,
    #utility,
    #phpinfo_menu {
        margin: 0;
        padding: 10px;
        border: 1px solid #000;
        background: #f5f5f5;
        text-align: justify;
        }
    #explain {
        margin: 0 0 10px 0;
        background: #eff5fb;
        }
    #explain_disabled {
        border: 1px solid #f00;
        background: #ffffd8;
        color: #f00;
        }
    #current {
        padding: 5px;
        border-bottom: 0;
        }
    #utility {
        margin: 0 0 10px 0;
        padding: 20px;
        background: #fff;
        }
    #phpinfo_menu {
        text-align: center;
        }
    .row {
        width: 95%;
        clear: both;
        }
    .row_left {
        float: left;
        margin: 0 0 5px 0;
        width: 200px;
        text-align: right;
        font-weight: bold;
        color: #1e74cb;
        }
    .row_right {
        float: left;
        margin: 0 0 5px 10px;
        text-align: left;
        font-size: 9pt;
        }
    #search {
        padding: 10px 10px 0 10px;
        border: 1px solid #000;
        background: #eff5fb;
        }
    #search_q,
    #search_b { /* Search input text */
        border: 1px solid #1e74cb;
        font-size: 10pt;
        }
    #search_b { /* Search button */
        border: 1px solid #1e74cb;
        background: #85b7ea;
        font-weight: bold;
        color: #fff;
        }
    #footer {
        clear: both;
        padding: 10px;
        border-top: 1px solid #000;
        background: #1e74cb;
        letter-spacing: 4px;
        text-transform: uppercase;
        text-align: center;
        font: bold 6pt tahoma;
        color: #85b7ea;
        }
    #footer a:link,
    #footer a:visited {
        text-decoration: none;
        color: #85b7ea;
        }
    #footer a:hover {
        color: #fff;
        }
    .codebox {
        background: #ffffd8;
        }
    .center {
        text-align: center;
        }
    .left {
        text-align: left;
        }
    .right {
        text-align: right;
        }
    .red {
        color: #f00;
        }
    .blue {
        color: #1e74cb;
        }
    .submit {
        font-weight: bold;
        letter-spacing: 2px;
        }
</style>'."\n\n";
        // Return the styles.
        return $css;
    }
    // Markup the Recovery Console main menu.
    public function markupMenu()
    {
        // Open the left column container.
        $menu  = '    <div id="left_column">'."\n\n";
        // Open the navigation block container.
        $menu .= '        <div id="navblock">';
        // Main link.
        $menu .= '<strong>'.$this->markupMenuLink('','',            _ZRC_TXT_NAV_MAIN_SHORT, _ZRC_TXT_NAV_MAIN_LONG).'</strong>';
        // Additional links.
        $menu .= $this->markupMenuLink('recover',   'theme',        _ZRC_TXT_NAV_THEME);
        $menu .= $this->markupMenuLink('recover',   'permission',   _ZRC_TXT_NAV_PERMISSION);
        $menu .= $this->markupMenuLink('recover',   'block',        _ZRC_TXT_NAV_BLOCK);
        $menu .= $this->markupMenuLink('recover',   'site',         _ZRC_TXT_NAV_DISABLEDSITE);
        $menu .= $this->markupMenuLink('recover',   'password',     _ZRC_TXT_NAV_PASSWORD);
        $menu .= $this->markupMenuLink('phpinfo',   '',             _ZRC_TXT_NAV_PHPINFO);
        $menu .= $this->markupMenuLink('about',     '',             _ZRC_TXT_NAV_ABOUT);
        // Closing the navigation block container.
        $menu .= '</div>'."\n\n";
        // Add the lockdown timer.
        $menu .= '        <div id="timer_container"></div>'."\n\n";
        // Close the left column container.
        $menu .= '    </div>'."\n\n";
        // Return the created markup.
        return $menu;
    }
    // Markup the Recovery Console main menu links.
    public function markupMenuLink($op=false, $utility=false, $text, $title=false)
    {
        // Check if this link is a database-requiring utility.
        $dba_reqd = (in_array($utility, $this->getAllDatabaseUtilities())) ? true : false;
        // Set an asterisk for use when dealing with database-requiring utilities.
        $asterisk = ($dba_reqd) ? '*' : null;
        // Ensure alt/title text is set and cast.
        $title = ($title) ? (string)$title : (string)$text;
        // Assign the base link target.
        $action = _ZRC_APP_SCRIPT;
        // Set style of selected links based on $op's presence.
        $style = (empty($this->operation)) ? 'class="selected" ' : null;
        // LINK RETURN POINT: Link is disabled due to incompatibility.
        // -----
        // Return a dimmed link when incompatibility exists.
        if (!$this->appCompatible()) {
            return $anchor = '<a '.$style.'href="'.$action.'"><del title="'. $title.'">'.$text.$asterisk.'</del></a>';
        }
        // LINK RETURN POINT: Main un-argumented link
        // -----
        // If no operation present (ie, Main) return link here.
        if (!$op) {
            return $anchor = '<a '.$style.'href="'.$action.'" title="'. $title.'">'.$text.'</a>';
        }
        // Attach operation to action string.
        $action .= '?op='.$op;
        // Re-assign the selected style based on $op.
        $style = ($op === $this->operation) ? 'class="selected"' : null;
        // LINK RETURN POINT: Link has $op arg only (ie, about, phpinfo).
        // -----
        if (!$utility) {
            return $anchor = '<a '.$style.'href="'.$action.'" title="'. $title.'">'.$text.'</a>';
        }
        // Attach utility to action string.
        $action .= '&amp;utility='.$utility;
        // Reassign the selected style based on $utility.
        $style = ($utility == $this->utility) ? 'class="selected" ' : null;
        // LINK RETURN POINT: Link with $op and $utility, requires DB.
        // Check if database access is required for this utility.
        if ($dba_reqd) {
            // Return a "dimmed" link if the database connection failed.
            if (!$this->dbEnabled) {
                return $anchor = '<a '.$style.'href="'.$action.'"><del title="'. $title.'">'.$text.$asterisk.'</del></a>';
            }
            // Return a regular link with $op and $utility args if the database connection exists.
            return $anchor = '<a '.$style.'href="'.$action.'" title="'. $title.'">'.$text.$asterisk.'</a>';
        }
        // LINK RETURN POINT: Link with $op and $utility, requires no database access. (ie, dba credential encoding utility.)
        return $anchor = '<a '.$style.'href="'.$action.'" title="'. $title.'">'.$text.'</a>';
    }
    // Markup the Recovery Console content area.
    public function markupBodyContent()
    {
        // Open the body-column container.
        $content = '    <div id="body_column">';
        // These 2 $ops will be allowed even if app is incompatible with site, as a courtesy.
        if ($this->operation !== 'about' && $this->operation !== 'phpinfo') {
            // Check if app is compatible with site.
            if (!$this->appCompatible()) {
                // If not, append a message, close container and return; user goes no further.
                $content .= _ZRC_ERR_INCOMPATIBLE.'</div>'."\n\n";
                return $content;
            }
        }
        // This will help keep source markup neat. =)
        $content .= "\n\n";
        // Check if operation is invalid. (empty IS valid)
        if (!empty($this->operation) && !in_array($this->operation, $this->getAllOperations())) {
            // Kill certain properties.
            $this->op      = false;
            $this->utility = false;
            // Append a message, close container and return; user goes no further.
            $content .= _ZRC_ERR_INVALID_OPERATION.'</div>'."\n\n";
            return $content;
        }

        // Get and contain the title of the utility.
        $content .= '        <div id="page_title" title="'.$this->getTitle().'">'.$this->getTitle().'</div>'."\n\n";
        // Check if this particular utility requires db access.
        if (in_array($this->utility, $this->getAllDatabaseUtilities())) {
            // If so, check if database is ready.
            if (!$this->dbEnabled) {
                // If not, set descriptive message, close container & return; go no further.
                $content .= '        <div id="explain_disabled">'._ZRC_ERR_DBA_REQUIRED.'</div>'."\n\n";
                $content .= '    </div>'."\n\n";
                return $content;
            }
        }
        // A switch calls the methods that gather the page/utility content.
        switch($this->operation) {
            case 'about': // About page only requires explanation.
                $content .= '        <div id="explain">'.$this->getExplanation().'</div>'."\n\n";
                break;
            case 'phpinfo': // PHPinfo is processed; get utility, but not explanation.
                $content .= '        <div id="utility">'."\n\n".$this->getUtility().'        </div>'."\n\n";
                break;
            case 'recover':
                // Recoveries require explanation, current setting and utility.
                if (!in_array($this->utility, $this->getAllUtilities())) {
                    // Set message and escape early since utility is not valid.
                    $content .= '        <div id="explain">'._ZRC_ERR_INVALID_UTILITY.'</div>'."\n\n";
                    break;
                }
                // Add utility explanation.
                $content .= '        <div id="explain">'.$this->getExplanation().'</div>'."\n\n";
                // Check if form was submitted.
                if ($this->INPUT['submit']) {
                    // Process recovery.
                    $this->processRecovery();
                    // Add any marked-up notices to the output.
                    $content .= $this->markupNotices();
                }
                // Add current setting here (as it may have just changed).
                $content .= '        <div id="current">'.$this->getCurrentSetting().'</div>'."\n\n";
                // Add the utility content to the output.
                $content .= '        <div id="utility">'."\n\n".$this->getUtility().'        </div>'."\n\n";
                break;
            default:
                // Default cases will show the main overview explanation.
                $content .= '        <div id="explain">'.$this->getExplanation().'</div>'."\n\n";
                // Default cases will show the prepared overview.
                $content .= '        <div id="utility">'."\n\n".$this->getUtility().'        </div>'."\n\n";
                break;
        }
        // On all pages, append Zikula site-search feature.
        $content .= '    <div id="search">'.$this->markupSearchBox().'</div>';
        // Closing the body-column container.
        $content .= '    </div>'."\n\n";
        // Return the content string.
        return $content;
    }
    // Markup Zikula site-search.
    public function markupSearchBox()
    {
        // Markup a form to search the PN site.
        $search  = '<div><strong class="blue">'._ZRC_TXT_STILL_NEED_HELP.'</strong> '._ZRC_TXT_SEARCH_THE_DOTCOM.'</div>'."\n\n";
        $search .= '<form method="post" action="http://community.zikula.org/index.php" style="display:inline;">
                    <div>
                        <img src="images/icons/extrasmall/search.gif" style="float:left;margin:2px 5px 0 0;" alt="'._ZRC_TXT_SEARCH.'" />
                        <input id="search_q" type="text" name="q" size="50" maxlength="255" />
                        <input type="hidden" name="name" value="Search" />
                        <input type="hidden" name="action" value="search" />
                        <input type="hidden" name="overview" value="1" />
                        <input type="hidden" name="active_pagesetter" id="active_pagesetter" value="1" />
                        <input type="hidden" name="active_downloads" id="active_downloads" value="1" />
                        <input type="hidden" name="active_weblinks" id="active_weblinks" value="1" />
                        <input type="hidden" name="active_ezcomments" id="active_ezcomments" value="1" />
                        <input type="hidden" name="active_stories" id="active_stories" value="1" />
                        <input type="hidden" name="stories_topics[]" id="stories_topics" value="" />
                        <input type="hidden" name="stories_cat[]" id="stories_cat" value="" />
                        <input type="hidden" name="active_pnForum" id="active_pnForum" value="1" />
                        <input type="hidden" name="pnForum_startnum" value="0" />
                        <input type="hidden" name="pnForum_forum[]" id="pnForum_forum" value="-1" />
                        <input type="hidden" name="pnForum_order" id="pnForum_order" value="1" />
                        <input type="hidden" name="active_users" id="active_users" value="1" />
                        <input type="hidden" name="active_comments" id="active_comments" value="1" />
                        <input type="hidden" name="startnum" value="0" />
                        <input type="hidden" name="total" value="" />
                        <input type="hidden" name="numlimit" value="250" />
                        <input type="hidden" name="bool" value="AND" />
                        <input type="submit" id="search_b" value="'._ZRC_TXT_SEARCH_BTN.'" title="'._ZRC_TXT_SEARCH.'" />
                    </div>
                </form>';
        // Return search form markup.
        return $search;
    }
    // Markup the Recovery Console page footer.
    public function markupFooter()
    {
        // Footer container and anchors.
        $footer  = '    <div id="footer">';
        $footer .= '<a href="http://code.zikula.org/core" title="'._ZRC_APP_TITLE.' v.'._ZRC_APP_VERSION.'">'._ZRC_APP_TITLE.' v.'._ZRC_APP_VERSION.'</a>';
        $footer .= '<br /><br />';
        $footer .= '<img src="images/powered/small/cms_zikula.png" alt="Zikula-logo" />&nbsp;<img src="images/powered/small/php_powered.png" alt="PHP-Logo"  />';
        // Closing the footer container.
        $footer .= '</div>'."\n\n";
        // Closing the main container.
        $footer .= '</div>'."\n\n";
        // Finish off the page markup.
        $footer .= '</body>'."\n\n".'</html>';
        // Returning the markup.
        return $footer;
    }
    // Markup notices, which consist of status and error messages and recovery output (if any).
    public function markupNotices()
    {
        if (!$this->INPUT['submit']) {
            return;
        }
        // Defaulted for loop purposes below.
        $errors = array();
        $status = array();
        $output = array();
        // Get any errors messages, status messages or recovery output.
        $errors = $this->getErrors();
        $status = $this->getStatus();
        $output = $this->getRecoveryOutput();
        // If no errors/status msgs, return here.
        if (empty($errors) && empty($status)) {
            return;
        }
        // Initialization.
        $markup = '';
        // --------------------
        // DO ERROR MESSAGES
        // --------------------
        // Check if errors exist.
        if (!empty($errors)) {
            // Container.
            $markup .= '        <div id="error">'."\n\n";
            // Short text title.
            $markup .= '<strong>'._ZRC_TXT_ERROR.'</strong><br />'."\n";
            // Start an unordered list.
            $markup .= '<ul>'."\n";
            // Converting errors into list items.
            foreach ($errors as $msg) {
                $markup .= '<li>' . $msg . '</li>'."\n";
            }
            // End the unordered list.
            $markup .= '</ul>'."\n";
            // Closing container.
            $markup .= '        </div>'."\n\n";
            // Return markup string.
            return $markup;
        }
        // --------------------
        // DO STATUS MESSAGES
        // --------------------
        if (!empty($status)) {
            // Container.
            $markup .= '        <div id="status">'."\n\n";
            // Short text title.
            $markup .= '<strong>'._ZRC_TXT_STATUS.'</strong><br />'."\n";
            // Start an unordered list.
            $markup .= '<ul>'."\n";
            // Converting errors into list items.
            foreach ($status as $msg) {
                $markup .= '<li>' . $msg . '</li>'."\n";
            }
            // End the unordered list.
            $markup .= '</ul>'."\n";
            // --------------------
            // DO ANY RECOVERY OUTPUT
            // --------------------
            if (!empty($output)) {
                // For consistence.
                $markup .= '<pre>'."\n";
                // Converting output to lines.
                foreach ($output as $line) {
                    $markup .= $line . "\n";
                }
                // End preformatting.
                $markup .= '</pre>'."\n";
            }
            // Closing container.
            $markup .= '        </div>'."\n\n";
        }
        // Return the markup.
        return $markup;
    }
    // Rendering the Recovery Console.
    public function renderRecoveryConsole()
    {
        // Assemble the markup.
        $console = $this->markupHeader()
                 . $this->markupMenu()
                 . $this->markupBodyContent()
                 . $this->markupFooter();
        // A single print is used in the class.
        return print $console;
    }

    // Return host name.
    public function getHost()
    {
        // Assign default host.
        $host = $_SERVER['HTTP_HOST'];
        // Check if $host is empty.
        if (empty($host)) {
            // Re-assign host.
            $host = $_SERVER['SERVER_NAME'];
            // Append port to host if port is other than 80;
            if ($_SERVER['SERVER_PORT'] != '80') {
                $host .= ':'.$_SERVER['SERVER_PORT'];
            }
        }
        // Return the host.
        return $host;
    }
    // Return server protocol: http or https.
    public function getServerProtocol()
    {
        // Check if protocol is basic http.
        if (preg_match('/^http:/', $_SERVER['REQUEST_URI'])) {
            return 'http';
        }
        // If set, assign HTTPS value, else null.
        $HTTPS = (isset($_SERVER['HTTPS'])) ? $_SERVER['HTTPS'] : null;
        // Check if HTTPS server value is present.
        if (!empty($HTTPS)) {
            // For IIS, a clause to ensure compatibility.
            if ($HTTPS != 'off') {
                return 'https';
            }
        }
        // Generic return.
        return 'http';
    }
    // Get title of Recovery Console page/operation.
    public function getTitle()
    {
        // Return main console (long) title if no operation or utility present.
        if (!$this->operation && !$this->utility) {
            return $title = _ZRC_TXT_NAV_MAIN_LONG;
        }
        // Array of titles, keyed by operations.
        $titles = array('about'         => _ZRC_TXT_NAV_ABOUT,
                        'theme'         => _ZRC_TXT_NAV_THEME,
                        'permission'    => _ZRC_TXT_NAV_PERMISSION,
                        'block'         => _ZRC_TXT_NAV_BLOCK,
                        'password'      => _ZRC_TXT_NAV_PASSWORD,
                        'phpinfo'       => _ZRC_TXT_NAV_PHPINFO,
                        'site'          => _ZRC_TXT_NAV_DISABLEDSITE,
                        );
        // Return title based on operation if utility not present.
        if (!$this->utility) {
            return $title = $titles[$this->operation];
        }
        // Utility present, return title based on utility.
        return $title = $titles[$this->utility];
    }
    // Get any explanatory texts.
    public function getExplanation()
    {
        // Array of explanatoty defines, keyed by operations.
        $explanations = array('theme'         => _ZRC_EXP_THEME,
                              'permission'    => _ZRC_EXP_PERMISSION,
                              'block'         => _ZRC_EXP_BLOCK,
                              'site'          => _ZRC_EXP_SITE,
                              'password'      => _ZRC_EXP_PASSWORD,
                              'about'         => _ZRC_EXP_ABOUT_TXT_INFO
                                                ._ZRC_EXP_ABOUT_TXT_VERSION
                                                ._ZRC_EXP_ABOUT_TXT_LICENSE
                                                ._ZRC_EXP_ABOUT_TXT_CREDITS,
                              );
        // Initialization.
        $explanation = '';
        // If no op or utility, assume main explanation is needed; return such.
        if (!$this->operation && !$this->utility) {
            return $explanation = _ZRC_EXP_MAIN;
        }
        // ...or if op exists and utility does not, get explanation based on op.
        if ($this->operation && !$this->utility) {
            $explanation .= $explanations[$this->operation];
            return $explanation;
        }
        // ...if op and util are set and valid, get explanation based on utility instead.
        if (in_array($this->operation, $this->getAllOperations()) &&
            in_array($this->utility, $this->getAllUtilities())) {
            $explanation .= $explanations[$this->utility];
        }
        // Return explanation.
        return $explanation;
    }
    // Get a current setting for a given recovery item.
    public function getCurrentSetting()
    {
        // A short descriptive title.
        $setting = '<strong>'._ZRC_TXT_CURRENT_SETTING.'</strong> ';
        // Switch to get proper text.
        switch($this->utility) {
            case 'theme':
                $setting .= $this->siteTheme;
                break;
            case 'permission':
                $setting .= _ZRC_TXT_NOTHING_TO_REPORT;
                break;
            case 'block':
                $setting .= (count($this->blocks) == 0) ? _ZRC_TXT_BLOCKS_NOT_FOUND : _ZRC_TXT_BLOCKS_OUTLINED_BELOW;
                break;
            case 'site':
                $setting .= ($this->siteInactive == false) ? _ZRC_TXT_SITE_ENABLED: _ZRC_TXT_SITE_DISABLED;
                break;
            case 'password':
                $setting .= _ZRC_TXT_NOTHING_TO_REPORT;
                break;
            default:
                $setting .= _ZRC_TXT_NOTHING_TO_REPORT;
                break;
        }
        // Return the current setting text.
        return $setting;
    }

    // Hub to get utilities.
    public function getUtility()
    {
        // A so-called "main" page clause if no op exists.
        if (!$this->operation) {
            return $form = $this->getMarkedUpOverview();
        }
        // PHPinfo needs no form, return marked-up PHPinfo instead.
        if ($this->operation === 'phpinfo') {
            return $form = $this->getMarkedUpPHPInfo();
        }

        // Check if this utility should be disabled.
        if ($this->getUtilityState() == false) {
            // Start off with an explanatory text.
            $form = _ZRC_TXT_UTILITY_DISABLED;
            // If utility disabled because it just ran, check for recovery-related output.
            $output = $this->getRecoveryOutput();
            // If no recovery output, return here.
            if (empty($output)) {
                return $form;
            }
            // Loop through recovery output.
            foreach ($output as $line) {
                $form .= ' '.$line;
            }
            return $form;
        }
        // ...else a site-fixing form should be built.  Every form comes with confirm and submit;
        // any additional inputs are formatted for display in getUtilityInputs* for auto-inclusion.
        // Start the form.
        $form = '            <form method="post" action="'._ZRC_APP_SCRIPT.'?op='.$this->operation.'&amp;utility='.$this->utility.'" enctype="application/x-www-form-urlencoded">'."\n\n";
        // If any additional inputs/markup are available for this utility, get them.
        if (method_exists($this, $method='getUtilityInputs'.$this->utility)) {
            $form .= $this->$method();
        }
        // Confirmation and submit inputs.
        $form .= '                <div class="row"><div class="row_left"><label for="confirm">'._ZRC_TXT_CLICK_TO_CONFIRM.'</label></div><div class="row_right"><input id="confirm" type="checkbox" name="confirm" value="1" /></div></div>'."\n\n";
        $form .= '                <div class="row"><div class="row_left"><label for="submit">&nbsp;</label></div><div class="row_right"><input class="submit" type="submit" name="submit" id="submit" value="'._ZRC_TXT_RUN_UTILITY.'" /></div></div>'."\n\n";
        $form .= '                <div style="clear:both;"></div>'."\n\n";
        // Close the form and container.
        $form .= '            </form>'."\n\n";
        // Return the form.
        return $form;
    }
    // Determine if a utility should be disabled.
    public function getUtilityState()
    {
        // Default the utility state to 'enabled' = true.
        $state = true;
        // Utilities for non-broken items can be disabled here.
        switch($this->utility) {
            case 'block':
                // If site has no blocks, disable this utility.
                if (count($this->blocks) == 0) {
                    $this->setRecoveryOutput(_ZRC_TXT_UTILITY_DISABLED_BLOCK);
                    $state = false;
                }
                break;
            case 'site':
                // if site is enabled, disable this utility
                if(System::getVar('siteoff') ==0) {
                    $this->setRecoveryOutput(_ZRC_TXT_UTILITY_DISABLED_SITE);
                    $state = false;
                }
            default:
                break;
        }
        // Return the state of this utility.
        return $state;
    }

    // enable site
    public function getUtilityInputsSite()
    {
        return '<p>'._ZRC_TXT_SITE_TURNITON.'</p>';
    }

    // Get inputs for theme recovery.
    public function getUtilityInputsTheme()
    {
        // First, create a dynamic selector.
        $selector = '<select name="theme" id="theme" size="1">'."\n";
        // Loop through all themes.
        foreach ($this->themes as $type => $themes) {
            // Skip any numerically-keyed elements.
            if (is_numeric($type)) {
                continue;
            }
            // Assign optgroup text.
            $label = ($type=='corethemes') ? _ZRC_TXT_THEME_CORETHEMES : _ZRC_TXT_THEME_AUTOTHEMES;
            // Create an optgroup for the theme type.
            if (count($themes) == 0) {
                continue;
            }
            $selector .= '<optgroup label="'.$label.'">'."\n";
            // Loop through the themes of this type.
            foreach ($themes as $theme) {
                if($theme['state'] == 1) {
                    // Check if form was submitted.
                    if (!$this->INPUT['submit']) {
                        // Default; for selecting the currently set theme in the list.
                        $selected = ($theme['name'] == $this->siteTheme) ? 'selected="selected"' : null;
                    } else {
                        // When submitted, for selecting the selected theme in the list.
                        $selected = ($theme['name'] == $this->INPUT['theme']) ? 'selected="selected"' : null;
                    }
                    // Add option to selector.
                    $selector .= '<option label="'.$theme['name'].'" value="'.$theme['name'].'" '.$selected.'>'.$theme['name'].'</option>'."\n";
                }
            }
            // Ending the optgroup.
            $selector .= '</optgroup>'."\n";
        }
        // Ending the selector.
        $selector .= '</select>'."\n";
        // Create the "utility" portion of the form and return it.
        $form  = '                <div class="row"><div class="row_left"><label for="themes">'._ZRC_TXT_THEME_AVAILABLE.'</label></div><div class="row_right">'.$selector.'</div></div>'."\n";
        $form .= '                <div class="row"><div class="row_left"><label for="resetusers">'._ZRC_TXT_THEME_RESET_USERS.'</label></div><div class="row_right"><input id="resetusers" type="checkbox" name="resetusers" value="1"'.(($this->INPUT['resetusers'] && $this->getErrors()) ? ' checked="checked"' : null).'  /></div></div>'."\n";
        return $form;
    }

    // Get additional inputs for permission recovery.
    public function getUtilityInputsPermission()
    {
        // Cheating a little; there are no actual inputs here.
        $form  = '<pre style="font-size:7.25pt;">';
        $form .= '<strong style="font-size:9pt;">'._ZRC_TXT_PERMISSION_EXAMPLE.'</strong>'."\n";
        $form .= '<div class="bar"><strong>     GROUP      |      COMPONENT      |    INSTANCE   |  PERMISSION LEVEL</strong></div>';
        $form .= ' Administrators |        .*           |      .*       |     Admin'."\n";
        $form .= '   All Groups   | ExtendedMenublock:: |      1:2:     |     None'."\n";
        $form .= '     Users      |        .*           |      .*       |     Comment'."\n";
        $form .= '  Unregistered  | ExtendedMenublock:: |    1:(1|3):   |     None'."\n";
        $form .= '  Unregistered  |        .*           |      .*       |     Read'."\n";
        $form .= '</pre>';
        return $form;
    }

    // Get additional inputs for block recovery.
    public function getUtilityInputsBlock()
    {
        $form  = '<table width="100%" border="1" style="border-collapse:collapse;" cellpadding="3" cellspacing="0">';
        $form .= '<tr style="background:#f2f2f2;">'."\n\n";
        $form .= '<th>'._ZRC_TXT_BLOCK_BID.'</th>'."\n\n";
        $form .= '<th>'._ZRC_TXT_BLOCK_KEY.'</th>'."\n\n";
        $form .= '<th width="100%">'._ZRC_TXT_BLOCK_TITLE.'</th>'."\n\n";
        $form .= '<th>'._ZRC_TXT_BLOCK_STATE.'</th>'."\n\n";
        $form .= '<th>'._ZRC_TXT_BLOCK_MID.'</th>'."\n\n";
        $form .= '<th>'._ZRC_TXT_BLOCK_NOCHANGE.'</th>'."\n\n";
        $form .= '<th>'._ZRC_TXT_BLOCK_DEACTIVE.'</th>'."\n\n";
        $form .= '<th>'._ZRC_TXT_BLOCK_DELETE.'</th>'."\n\n";
        $form .= '</tr>'."\n\n";
        $row=0;
        foreach ($this->blocks as $block) {
            $class = ($row++ & 1) ? ' style="background:#fafafa;"' : null;
            $form .= '<tr'.$class.'>'."\n\n";
            $form .= '<td class="center">'.$block['bid'].'</td>'."\n\n";
            $form .= '<td>'.$block['bkey'].'</td>'."\n\n";
            $form .= '<td>'.$block['title'].'</td>'."\n\n";
            $form .= '<td class="center">'.(($block['active']) ? '<img src="images/icons/extrasmall/greenled.gif" alt="'.$block['title'].' :: '._ZRC_TXT_BLOCK_ACTIVE.'" title="'.$block['title'].' :: '._ZRC_TXT_BLOCK_ACTIVE.'" />' : '<img src="images/icons/extrasmall/yellowled.gif" alt="'.$block['title'].' :: '._ZRC_TXT_BLOCK_INACTIVE.'" title="'.$block['title'].' :: '._ZRC_TXT_BLOCK_INACTIVE.'" />').'</td>'."\n\n";
            $form .= '<td class="center">'.$block['mid'].'</td>'."\n\n";
            $form .= '<td class="center"><input type="radio" name="blocks['.$block['bid'].']" value="0"'.((empty($this->INPUT['blocks'][$block['bid']])) ? ' checked="checked"' : null).' /></td>'."\n\n";
            if ($block['active'] != 0) {
                $form .= '<td class="center"><input type="radio" name="blocks['.$block['bid'].']" value="1"'.(($this->INPUT['blocks'][$block['bid']]==1) ? ' checked="checked"' : null).' /></td>'."\n\n";
            } else {
                $form .= '<td class="center">'._ZRC_TXT_NOT_APPLICABLE_ABBR.'</td>';
            }
            $form .= '<td class="center"><input type="radio" name="blocks['.$block['bid'].']" value="2"'.(($this->INPUT['blocks'][$block['bid']]==2) ? ' checked="checked"' : null).' /></td>'."\n\n";
            $form .= '</tr>'."\n\n";
        }
        $form .= '</table><br />'."\n\n";
        //$form .= '                <div class="row"><div class="row_left"><label for="resetblocks">'._ZRC_TXT_BLOCK_RESET.'</label></div><div class="row_right"><input id="resetblocks" type="checkbox" name="resetblocks" value="1" /></div></div>'."\n";
        return $form;
    }

    // Get additional inputs for password reset.
    public function getUtilityInputsPassword()
    {
        $form   = '                <div class="row"><div class="row_left"><label for="username">'._ZRC_TXT_PASSWORD_UNAME.'</label></div><div class="row_right"><input type="text" id="username" name="username" value="admin" /></div></div>'."\n";
        $form  .= '                <div class="row"><div class="row_left"><label for="password1">'._ZRC_TXT_PASSWORD_UPASS.'</label></div><div class="row_right"><input type="password" id="password1" name="password1" /></div></div>'."\n";
        $form  .= '                <div class="row"><div class="row_left"><label for="password2">'._ZRC_TXT_PASSWORD_UPASSAGAIN.'</label></div><div class="row_right"><input type="password" id="password2" name="password2" /></div></div>'."\n";

        return $form;
    }

    // Get formatted PHP info.
    public function getMarkedUpPHPInfo()
    {
        // Get chosen PHP view or default it.
        $view = (isset($this->INPUT['view'])) ? (int)$this->INPUT['view'] : 64;
        if ($view <> 0) {
            // Start an output object to hold generated markup.
            ob_start();
            // Run PHP info function.
            phpinfo($view);
            // Gather the output for manipulation.
            $phpinfo = ob_get_contents();
            // No further need of the output object.
            ob_end_clean();
            // Define regexes to be replaced and corresponding replacements, then replace.
            $regexes = array('/^.*<body[^>]*>/is', '/<hr[^>]*>/i', '/<a href="http:\/\/([-\w\.]+)+(:\d+)?(\/([\w\/_\.]*(\?\S+)?)?)?"><img .*?\/><\/a>/i', '/ class="p"/i', '/width="[0-9]+"/i', '/<\/body[^>]*>.*$/is');
            $replace = array('', '', '', '', 'width="100%"', '');
            $phpinfo = preg_replace($regexes, $replace, $phpinfo);
            // Define strings to be replaced and corresponding replacements, then replace.
            $strings = array('<table border="0" cellpadding="3" width="80%">', '<tr class="h">');
            $replace = array('<table border="0" width="100%">', '<tr>');
            $phpinfo = str_replace($strings, $replace, $phpinfo);
        } else {
            $phpinfo = '<h2>PHP '.phpversion().'</h2>';
        }
        // Create a submenu for filtering PHP information.
        $form  = '<div id="phpinfo_menu" class="center">'."\n";
        $form .= '[ <a href="'._ZRC_APP_SCRIPT.'?op=phpinfo&amp;view=0" title="'._ZRC_TXT_NAV_PHPINFO_VERSION.'">'._ZRC_TXT_NAV_PHPINFO_VERSION.'</a> ]';
        $form .= '[ <a href="'._ZRC_APP_SCRIPT.'?op=phpinfo&amp;view=4" title="'._ZRC_TXT_NAV_PHPINFO_CORE.'">'._ZRC_TXT_NAV_PHPINFO_CORE.'</a> ]';
        $form .= '[ <a href="'._ZRC_APP_SCRIPT.'?op=phpinfo&amp;view=32" title="'._ZRC_TXT_NAV_PHPINFO_VARIABLES.'">'._ZRC_TXT_NAV_PHPINFO_VARIABLES.'</a> ]';
        $form .= '[ <a href="'._ZRC_APP_SCRIPT.'?op=phpinfo&amp;view=64" title="'._ZRC_TXT_NAV_PHPINFO_LICENSE.'">'._ZRC_TXT_NAV_PHPINFO_LICENSE.'</a> ]'."\n";
        $form .= '<br />';
        $form .= '[ <a href="'._ZRC_APP_SCRIPT.'?op=phpinfo&amp;view=16" title="'._ZRC_TXT_NAV_PHPINFO_ENVIRONMENT.'">'._ZRC_TXT_NAV_PHPINFO_ENVIRONMENT.'</a> ]';
        $form .= '[ <a href="'._ZRC_APP_SCRIPT.'?op=phpinfo&amp;view=8" title="'._ZRC_TXT_NAV_PHPINFO_APACHE.'">'._ZRC_TXT_NAV_PHPINFO_APACHE.'</a> ]';
        $form .= '</div>'."\n";
        // Add previously resulting string to object's markup here.
        $form .= $phpinfo;
        // Return the "form".
        return $form;
    }
    // Get formatted overview.
    public function getMarkedUpOverview()
    {
        // Assemble and return the form markup.
        $form  = '            <div class="row"><div class="row_left">'._ZRC_TXT_OVERVIEW_VERSION.'</div><div class="row_right">'.$this->siteCodebase.' '.$this->siteVersion.'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'._ZRC_TXT_OVERVIEW_CONFIG.'</div><div class="row_right">'.$this->siteConfigFile.'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'._ZRC_TXT_OVERVIEW_STATUS.'</div><div class="row_right">'.(($this->siteInactive) ? '<img src="images/icons/extrasmall/redled.gif" />'._ZRC_TXT_OVERVIEW_SITE_OFF :'<img src="images/icons/extrasmall/greenled.gif" />'._ZRC_TXT_OVERVIEW_SITE_ON).'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'._ZRC_TXT_OVERVIEW_DATABASE.'</div><div class="row_right">'.(($this->dbEnabled) ? '<img src="images/icons/extrasmall/greenled.gif" />'._ZRC_TXT_OVERVIEW_CONNECTED : '<img src="images/icons/extrasmall/redled.gif" />'._ZRC_TXT_OVERVIEW_NOT_CONNECTED).'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'._ZRC_TXT_OVERVIEW_LANGUAGE.'</div><div class="row_right">'.$this->siteLang.'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'._ZRC_TXT_OVERVIEW_DETECTED_THEMES.'</div><div class="row_right">'.$this->getMarkedUpOverviewThemeList().'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'._ZRC_TXT_OVERVIEW_DETECTED_MODS.'</div><div class="row_right">'.$this->getMarkedUpOverviewModuleList().'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'._ZRC_TXT_OVERVIEW_DETECTED_BLOCKS.'</div><div class="row_right">'.$this->getMarkedUpOverviewBlockList().'</div></div>'."\n\n";
        $form .= '            <div style="clear:both;"></div>'."\n\n";
        return $form;
    }
    // Get marked up theme list for overview page.
    public function getMarkedUpOverviewThemeList()
    {
        // Initialization.
        $list = '';
        // Clause for when no themes came back.
        if (!isset($this->themes['corethemes']) && !isset($this->themes['autothemes'])) {
            return $list = _ZRC_TXT_NONE_DETECTED;
        }

        if (!empty($this->themes['corethemes'])) {
            $list .= '<strong>'._ZRC_TXT_OVERVIEW_CORETHEMES.'</strong>';
            $list .= $this->getMarkedUpOverviewThemeListReal($this->themes['corethemes']);
        }
        if (!empty($this->themes['autothemes'])) {
            $list .= '<strong>'._ZRC_TXT_OVERVIEW_AUTOTHEMES.'</strong>';
            $list .= $this->getMarkedUpOverviewThemeListReal($this->themes['autothemes']);
        }
        return $list;
    }

    public function getMarkedUpOverviewThemeListReal($themesarray)
    {
        // Looping through core themes.
        $list .= '<ul>';
        foreach ($themesarray as $theme) {
            if ($theme['name'] === $this->siteTheme) {
                $list .= '<li><img src="images/icons/extrasmall/greenled.gif" alt="'.$theme['name'].' :: '._ZRC_TXT_OVERVIEW_ACTIVE.'" title="'.$theme['name'].' :: '._ZRC_TXT_OVERVIEW_DEFAULT_THEME.'" />';
            } else if ($theme['state'] == 1){
                $list .= '<li><img src="images/icons/extrasmall/yellowled.gif" alt="'.$theme['name'].' :: '._ZRC_TXT_OVERVIEW_INACTIVE.'" title="'.$theme['name'].' :: '._ZRC_TXT_OVERVIEW_INACTIVE.'" />';
            } else {
                $list .= '<li><img src="images/icons/extrasmall/redled.gif" alt="'.$theme['name'].' :: '._ZRC_TXT_OVERVIEW_UNINITIALIZED.'" title="'.$theme['name'].' :: '._ZRC_TXT_OVERVIEW_UNINITIALIZED.'" />';
            }
            // Append theme name.
            $list .= $theme['name'] . '</li>';
        }
        $list .= '</ul>';
        // Return markup string.
        return $list;
    }

    // Get marked up module list for overview page.
    public function getMarkedUpOverviewModuleList()
    {
        // Initialization.
        $list = '';
        // Clause for when no modules came back.
        if (!isset($this->modules['sys_mods']) && !isset($this->modules['usr_mods'])) {
            return $list = _ZRC_TXT_NONE_DETECTED;
        }

        $list .= '<strong>'._ZRC_TXT_OVERVIEW_SYSTEM_MODS.'</strong>';
        $list .= $this->getMarkedUpOverviewModuleListReal($this->modules['sys_mods']);
        $list .= '<strong>'._ZRC_TXT_OVERVIEW_3RDPARTY_MODS.'</strong>';
        $list .= $this->getMarkedUpOverviewModuleListReal($this->modules['usr_mods']);
        return $list;
    }

    public function getMarkedUpOverviewModuleListReal($modsarray)
    {
        $list = '';
        // Check if any modules exist.
        if (!empty($modsarray)) {
            $list .= '<ul>';
            // Loop through modules.
            foreach ($modsarray as $mod) {
                // Append module-state image.
                $list .= '<li>';
                switch($mod['state']) {
                    case PNMODULE_STATE_UNINITIALISED:
                        $list .= '<img src="images/icons/extrasmall/redled.gif" alt="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_UNINITIALIZED.'" title="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_UNINITIALIZED.'" />';
                        break;
                    case PNMODULE_STATE_INACTIVE:
                        $list .= '<img src="images/icons/extrasmall/yellowled.gif" alt="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_INACTIVE.'" title="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_INACTIVE.'" />';
                        break;
                    case PNMODULE_STATE_ACTIVE:
                        $list .= '<img src="images/icons/extrasmall/greenled.gif" alt="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_ACTIVE.'" title="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_ACTIVE.'" />';
                        break;
                    case PNMODULE_STATE_MISSING:
                        $list .= '<img src="images/icons/extrasmall/14_layer_deletelayer.gif" alt="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_FILESMISSING.'" title="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_FILESMISSING.'" />';
                        break;
                    case PNMODULE_STATE_UPGRADED:
                        $list .= '<img src="images/icons/extrasmall/agt_update-product.gif" alt="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_UPGRADED.'" title="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_UPGRADED.'" />';
                        break;
                    case PNMODULE_STATE_INVALID:
                    default:
                        $list .= '<img src="images/icons/extrasmall/14_layer_deletelayer.gif" alt="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_INVALID.'" title="'.$mod['name'].' :: '._ZRC_TXT_OVERVIEW_INVALID.'" />';
                }
                // Append module name.
                $list .= ' '.$mod['name'].'</li>';
            }
            $list .= '</ul>';
        }
        // Return markup string.
        return $list;
    }

    // Get marked up theme list for overview page.
    public function getMarkedUpOverviewBlockList()
    {
        // Clause for when no blocks came back.
        if (empty($this->blocks)) {
            return $list = _ZRC_TXT_NONE_DETECTED;
        }
        $list = '<ul>';
        // Loop through all blocks.
        foreach ($this->blocks as $block) {
            // Append module-state image.
            if ($block['active'] == 1) {
                $list .= '<li><img src="images/icons/extrasmall/greenled.gif" alt="'.$block['title'].' :: '._ZRC_TXT_OVERVIEW_ACTIVE.'" title="'.$block['title'].' :: '._ZRC_TXT_OVERVIEW_ACTIVE.'" />';
            } else {
                $list .= '<li><img src="images/icons/extrasmall/yellowled.gif" alt="'.$block['title'].' :: '._ZRC_TXT_OVERVIEW_INACTIVE.'" title="'.$block['title'].' :: '._ZRC_TXT_OVERVIEW_INACTIVE.'" />';
            }
            // Append block title.
            $list .= ' '.$block['title'].'</li>';
        }
        $list .= '</ul>';
        // Return markup string.
        return $list;
    }

    // Processing hub.
    public function processRecovery()
    {
        // Check if utility is valid.
        if (!in_array($this->utility, $this->getAllUtilities())) {
            $this->setError(_ZRC_ERR_INVALID_UTILITY);
            $this->INPUT = false;
            return false;
        }
        // Check for confirmation value.
        if ($this->INPUT['confirm'] != 1) {
            $this->setError(_ZRC_ERR_CONFIRM_REQUIRED);
            return false;
        }
        
        // Call a recovery method based on $this->utility.
        switch($this->utility) {
            case 'theme':
                $success = $this->processRecoveryTheme();
                break;
            case 'permission':
                $success = $this->processRecoveryPermission();
                break;
            case 'block':
                $success = $this->processRecoveryBlock();
                break;
            case 'site':
                $success = $this->processRecoverySite();
                break;
            case 'password':
                $success = $this->processRecoveryPassword();
                break;
            default:
                break;
        }
        // Report error on failure.
        if (!$success) {
            return false;
        }
        // Report successful recovery.
        return true;
    }

    // Process site recovery
    public function processRecoverySite()
    {
        // Check if form was submitted.
        if (!System::setVar('siteoff', (int)0)) {
            $this->setError(_ZRC_ERR_SITE_ENABLING);
            return false;
        }
        $this->siteInactive = false;
        // Set a status message.
        $this->setStatus(_ZRC_TXT_RECOVERY_SUCCESS);
        return true;
    }
    
    // Process recovery from theme errors.
    public function processRecoveryTheme()
    {
        // Get the new theme chosen.
        $theme = $this->INPUT['theme'];
        // If input theme is not a coretheme or AutoTheme, validity fails.
        if (!array_key_exists($theme, $this->themes['corethemes']) && !array_key_exists($theme, $this->themes['autothemes'])) {
            $this->setError(_ZRC_ERR_THEME_INVALID);
            return false;
        }
        // Set the new theme as default.
        System::setVar('Default_Theme', $theme);
        // Set the new theme to the object.
        $this->siteTheme = System::getVar('Default_Theme');
        // Check if user themes are to be reset too.
        if ($this->INPUT['resetusers']) {
            // Reset user themes.
            $this->resetUserThemes();
        }
        // If any errors, recovery failed.
        if ($this->getErrors()) {
            return false;
        }
        // Set a status message.
        $this->setStatus(_ZRC_TXT_RECOVERY_SUCCESS);
        // Recovery successful.
        return true;
    }
    
    // Process recovery from bad permissions.
    public function processRecoveryPermission()
    {
        // Reset permissions to default.
        $this->resetPermissions();
        // Check for errors, return false if any found.
        if ($this->getErrors()) {
            return false;
        }
        // Set a status message.
        $this->setStatus(_ZRC_TXT_RECOVERY_SUCCESS);
        return true;
    }

    // Process recovery from bad sideblocks.
    public function processRecoveryBlock()
    {
        // Determine if any action is required; default value.
        $action_required = false;

        // Ensure that block data is an array.
        if (!is_array($this->INPUT['blocks'])) {
            return false;
        }

        // Loop through blocks daga.
        foreach($this->INPUT['blocks'] as $bid=>$action) {
            // Skip to next block if no action specified.
            if (empty($action)) {
                continue;
            }
            // Set action as true; default, if here.
            $action_required = true;
            // Break out of the loop.
            break;
        }
        // Check if no action is required.
        if (!$action_required) {
            // Set status message and return false.
            $this->setStatus(_ZRC_ERR_NO_DATA_NO_CHANGE);
            return false;
        }

        // Loop through all blocks.
        foreach($this->INPUT['blocks'] as $bid => $action) {
            // Get information about the block.
            $block = ModUtil::apiFunc('Blocks', 'user', 'get', array('bid'=>$bid));
            // Check which action should be performed.
            if ($action == 1) { // 1 = disable block
                // Attempt block disable.
                if (!$this->disableBlock($bid)) {
                    // Set error.
                    $this->setError(_ZRC_TXT_BLOCK_DISABLE_FAILED.': '.$block['title']);
                    continue;
                }
                // Set status message.
                $this->setStatus(_ZRC_TXT_BLOCK_DISABLED.': '.$block['title']);
            } else if ($action == 2) { // 2 = delete block
                // Attempt block deletion.
                if (!$this->deleteBlock($bid)) {
                    // Set error.
                    $this->setError(_ZRC_TXT_BLOCK_DELETE_FAILED.': '.$block['title']);
                    continue;
                }
                // Set status message.
                $this->setStatus(_ZRC_TXT_BLOCK_DELETED.': '.$block['title']);
            }
        }
        // Return false if any errors.
        if (!$this->getErrors()) {
            return false;
        }
        // Set a status message.
        $this->setStatus(_ZRC_TXT_RECOVERY_SUCCESS);
        // Return true for success.
        return true;
    }

    // Process reset password.
    public function processRecoveryPassword()
    {
        // get variables
        $username = $this->INPUT['username'];
        $password1 = $this->INPUT['password1'];
        $password2 = $this->INPUT['password2'];

        // check that username is not empty
        if (empty($username)) {
            $this->setError(_ZRC_ERR_PASSWORD_EMPTYUNAME);
            return false;
        }

        // check that password is not empty
        if (empty($password1)) {
            $this->setError(_ZRC_ERR_PASSWORD_EMPTYPASS);
            return false;
        }

        // check that passwords match
        if ($password1 != $password2) {
            $this->setError(_ZRC_ERR_PASSWORD_MISMATCH);
            return false;
        }

         // check that username is not the anonymous one
        $anonymous = ModUtil::getVar('Users', 'anonymous');
        if ($username == $anonymous || $username == strtolower($anonymous)) {
            $this->setError(_ZRC_ERR_PASSWORD_ANONYMOUSUSERNAME);
            return false;
        }

        $table = System::dbGetTables();
        $userstable  = $table['users'];
        $userscolumn = $table['users_column'];

        // check that username exists
        $uid = DBUtil::selectField('users', 'uid', $userscolumn['uname']."='".$username."'");
        if (!$uid) {
            $this->setError(_ZRC_ERR_PASSWORD_INVALIDUSERNAME);
            return false;
        }
        
        // hash the password and check if it is valid
        $password = UserUtil::getHashedPassword($password1);
        if (!$password) {
            $this->setError(_ZRC_ERR_PASSWORD_INVALIDPASSWORD);
            return false;
        }

        // update the password
        // create the object
        $obj = array('uid' => $uid, 'pass' => $password);

        // perform update
        if (!DBUtil::updateObject($obj, 'users', '', 'uid')) {
            $this->setError(_ZRC_ERR_PASSWORD_RESETFAILED);
            return false;
        }

        // Set a status message.
        $this->setStatus(_ZRC_TXT_PASSWORD_RESETSUCCESS);

        // Recovery successful.
        return true;
    }

    // Set a status message.
    public function setStatus($msg)
    {
        $this->statusMessages[] = $msg;
    }
    // Set an error message.
    public function setError($msg)
    {
        $this->errorMessages[] = $msg;
    }
    // Set any recovery utility output message.
    public function setRecoveryOutput($msg)
    {
        $this->recoveryOutput[] = $msg;
    }

    // Return all status messages.
    public function getStatus()
    {
        return $status = $this->statusMessages;
    }
    // Return all error messages.
    public function getErrors()
    {
        return $errors = $this->errorMessages;
    }
    // Return any utility output texts.
    public function getRecoveryOutput()
    {
        return $output = $this->recoveryOutput;
    }

    // Return an array of all possible operations.
    public function getAllOperations()
    {
        return $valid = array('about', 'recover', 'phpinfo');
    }
    // Return an array of all possible utilities.
    public function getAllUtilities()
    {
        return $valid = array('theme', 'permission', 'block', 'site', 'password');
    }
    // Return an array of database-requiring utilities.
    public function getAllDatabaseUtilities()
    {
        return $valid = array('theme', 'permission', 'block', 'site', 'password');
    }

    // Disable a sideblock.
    public function disableBlock($bid)
    {
        // Ensure that $bid is 1 or higher.
        if ($bid < 1) {
            $this->setError(_ZRC_ERR_BLOCK_INVALID_BID);
            return false;
        }

        // Verify that block information was obtained.
        if (!BlockUtil::getBlockInfo($bid)) {
            $this->setError(_ZRC_ERR_BLOCK_NO_SUCH_BLOCK);
            return false;
        }

        // To be sure none more than active-state is changed.
        $obj = array('bid'=>$bid, 'active'=>0);

        // Attempt to disable the block.
        if (!DBUtil::updateObject ($obj, 'blocks', '', 'bid')) {
            $this->setError(_ZRC_ERR_BLOCK_NOT_DISABLED);
            return false;
        }

        // Success.
        return true;
    }
    // Delete a sideblock.
    public function deleteBlock($bid)
    {
        // Ensure that $bid is 1 or higher.
        if (!is_numeric($bid) || $bid < 1) {
            $this->setError(_ZRC_ERR_BLOCK_INVALID_BID);
            return false;
        }

        // Ensure block exists.
        if (!BlockUtil::getBlockInfo($bid)) {
            $this->setError(_ZRC_ERR_BLOCK_NO_SUCH_BLOCK);
            return false;
        }

        // Delete block placements for this block.
        if (!DBUtil::deleteObjectByID('block_placements', $bid, 'bid')) {
            $this->setError(_ZRC_ERR_BLOCK_NOT_DISPLACED);
            return false;
        }

        // Delete the block itself.
        if (!DBUtil::deleteObjectByID ('blocks', $bid, 'bid')) {
            $this->setError(_ZRC_ERR_BLOCK_NOT_DELETED);
            return false;
        }

        // Let other modules know we have deleted an item.
        ModUtil::callHooks('item', 'delete', $bid, array('module'=>'Blocks'));

        // Success.
        return true;
    }

    // Reset site permissions.
    public function resetPermissions()
    {
        // Delete all current permission entries.
        if (!DBUtil::truncateTable('group_perms')) {
            $this->setError(_ZRC_ERR_PERMISSION_NOT_DELETED);
            return false;
        }

        // Array of permission objects to insert.
        $perms = array();
        $perms[] = array('pid'=>1, 'gid'=>2,  'sequence'=>1, 'realm'=>0, 'component'=>'.*',                     'instance'=>'.*',           'level'=>800,   'bond'=>0);
        $perms[] = array('pid'=>2, 'gid'=>-1, 'sequence'=>2, 'realm'=>0, 'component'=>'ExtendedMenublock::',    'instance'=>'1:2:',         'level'=>0,     'bond'=>0);
        $perms[] = array('pid'=>3, 'gid'=>1,  'sequence'=>3, 'realm'=>0, 'component'=>'.*',                     'instance'=>'.*',           'level'=>300,   'bond'=>0);
        $perms[] = array('pid'=>4, 'gid'=>0,  'sequence'=>4, 'realm'=>0, 'component'=>'ExtendedMenublock::',    'instance'=>'1:(1|3):',     'level'=>0,     'bond'=>0);
        $perms[] = array('pid'=>5, 'gid'=>0,  'sequence'=>5, 'realm'=>0, 'component'=>'.*',                     'instance'=>'.*',           'level'=>200,   'bond'=>0);

        // Insert default permissions or fail.
        if (!DBUtil::insertObjectArray($perms, 'group_perms', 'pid')) {
            $this->setError(_ZRC_ERR_PERMISSION_INSERTION_FAILURE);
            return false;
        }

        // Success.
        return true;
    }
    // Reset user themes.
    public function resetUserThemes()
    {
        // Get all users.
        $users = ModUtil::apiFunc('Users', 'user', 'getall');
        // Loop through users.
        foreach ($users as $user) {
            // Create an update object.
            $obj = array('uid'=>$user['uid'], 'theme'=>'');
            // Update the user's record or fail.
            if (!DBUtil::updateObject($obj, 'users', '', 'uid')) {
                $this->setError(_ZRC_ERR_RESETTING_USER_THEMES);
                return false;
            }
        }
        // Success.
        return true;
    }

    // Handle a fatal error scenario.
    public function fatalError($msg)
    {
        // Header, error content, footer.
        $fatal  = $this->markupHeader();
        $fatal .= '<div id="fatal">'.(string)$msg.'</div>';
        $fatal .= $this->markupFooter();
        // Kill application.
        die ($fatal);
    }

} // End of Zikula Recovery Console class.
