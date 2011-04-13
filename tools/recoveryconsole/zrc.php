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
    const VERSION = '1.3.0';
    const EXPIRES = 1200;

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

    public function getBaseScript()
    {
        return basename($_SERVER['PHP_SELF']);
    }

    // Initialize the recovery console config and texts.
    public function initRecoveryConsole()
    {
        // Define config settings; will be used in text defines to some degree.
        $this->initAppConfigDefines();
    }
    // Define Recovery Console's main config settings.
    public function initAppConfigDefines()
    {
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
            $themeid = ThemeUtil::getIDFromName($theme);
            if ($themeid <> false) {
                $this->themes['corethemes'][$theme] = ThemeUtil::getInfo(ThemeUtil::getIDFromName($theme));
            } else {
                $this->themes['corethemes'][$theme] = array('name' => $theme, 'state' => 0);
            }
        }
        ksort($this->themes['corethemes']);

        //
        // get site status
        //
        $this->siteInactive = (System::getVar('siteoff') == 1);

        //
        // load all modules.
        //
        $mods = DBUtil::selectObjectArray('modules',"WHERE ({$this->dbTables['modules_column']['id']} > 0)",'type');
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

        // check temp directory
        $this->tempdir = System::getVar('temp');
        $tempdirsubs = array(
            'error_logs' => 0,
            'idsTmp' => 0,
            'purifierCache' => 0,
            'view_cache' => 0,
            'view_compiled' => 0,
            'Theme_cache' => 0,
            'Theme_compiled' => 0,
            'Theme_Config' => 0,
        );
        $this->tempdirsubsfailed = count($tempdirsubs);
        // Open the temp directory.
        $handle = opendir($this->tempdir);
        if (!$handle){
            $this->tempdirexists = false;
        } else {
            $this->tempdirexists = true;
            // Read the directory contents.
            // continue if file is not in our wanted array
            while ($dir = readdir($handle)) {
                if (!array_key_exists($dir, $tempdirsubs)) {
                    continue;
                }
                // update the status
                $tempdirsubs[$dir] = 1;
                $this->tempdirsubsfailed--;
            }
            // Close the directory.
            closedir($handle);
        }
        $this->tempdirsubs = $tempdirsubs;
        
        return true;
    }

    // Initialize Zikula and set relevant properties.
    public function initZikula()
    {
        // Assign path/System file.
        $file = 'lib/ZLoader.php';
        // Before inclusion, ensure the API file can be accessed.
        if (!file_exists($file) && !is_readable($file)) {
            $this->fatalError('<strong>THIS APPLICTATION WAS IMPROPERLY UPLOADED</strong><br />Please ensure that you have uploaded this file to<br />the <em>root directory</em> of your site and try again.');
        }
        // Include the API file.
        require_once 'lib/ZLoader.php';
        ZLoader::register();
        // Before init, avoid error; ensure the function exists.
        if (!is_callable(array('System', 'init'))) {
            $this->fatalError('<strong>Zikula COULD NOT BE INITIALIZED</strong><br />No further information is available.');
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

        if (!isset($this->appForceLocked)) {
            $this->appForceLocked = false;
        }
    }

    // Check if lockdown is engaged.
    public function initLockMechanism()
    {
        // Kill app if app is expired or force-locked by the system.
        if ($this->appIsExpired() || $this->appForceLocked) {
            $this->fatalError(__('<strong>THIS APPLICATION HAS EXPIRED</strong><br />Re-upload the file to reset the timer.'));
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
        if (!$this->dbTables = DBUtil::getTables()) {
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
            return (string)preg_replace('~\W+~', '', $input);
        }
        // Else dealing with an array.
        // Looping through array values.
        $cleaned = array();
        foreach ($input as $key=>$val) {
            // Special case: dba user/pass can contain more than text chars, do it inline.
            if ($key=='zuname' || $key=='zpass1' || $key=='zpass2') {
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
        return filemtime($this->getBaseScript());
    }
    // Return the unix time that this file should lockdown.
    public function appExpirationTime()
    {
        return $this->appCreationTime() + self::EXPIRES;
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
        $head .= '<title>'.'Zikula Recovery Console'.'</title>'."\n\n";
        // Countdown timer script.  For GUI purposes only; security is not dependent upon Javascript.
        $head .= $this->markupJavascript();
        // All CSS comes via this method.
        $head .= $this->markupStyles();
        $head .= '</head>'."\n\n";
        $head .= '<body>'."\n\n";
        $head .= '<div id="container">'."\n\n";
        $head .= '    <div id="app_title">'.'Zikula Recovery Console'.'</div>'."\n\n";
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
        $js .= '                document.getElementById("timer_container").innerHTML = \''.__('LOCKDOWN TIMER').'<div id="timer_digits">\'+mins+\':\' + secs + \'<\/div><div id="timer_reason">'.__('The Zikula Recovery Console will be automatically disabled when the timer expires.  If it expires before you finish your work, simply re-upload the file and refresh your browser.  The timer will be reset.').'<\/div>\';'."\n";
        $js .= '                setTimeout(function() { countDown(n - 1) }, 1000)'."\n";
        $js .= '            } else if (n == 0) {'."\n";
        $js .= '                document.getElementById("timer_container").innerHTML =  \''.__('LOCKDOWN ENGAGED').'<div id="timer_reason" class="red">'.__('The Zikula Recovery Console is now disabled from further use as a security precaution.  You must upload a new copy of this file to reset the lockdown timer.').'<\/div>\';'."\n";
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
        $menu .= '<strong>'.$this->markupMenuLink('','',            __('Main'), __('Configuration Overview')).'</strong>';
        // Additional links.
        $menu .= $this->markupMenuLink('recover',   'theme',        __('Theme Recovery'));
        $menu .= $this->markupMenuLink('recover',   'permission',   __('Permission Recovery'));
        $menu .= $this->markupMenuLink('recover',   'block',        __('Block Recovery'));
        $menu .= $this->markupMenuLink('recover',   'site',         __('Disabled Site Recovery'));
        $menu .= $this->markupMenuLink('recover',   'password',     __('Password Reset'));
        $menu .= $this->markupMenuLink('recover',   'tempdir',      __('Rebuild Temp Directory'));
        $menu .= $this->markupMenuLink('recover',   'outputfilter', __('Reset Output Filter'));
        $menu .= $this->markupMenuLink('recover',   'phpids',       __('Disable PHPIDS'));
        $menu .= $this->markupMenuLink('phpinfo',   '',             __('PHP Information'));
        $menu .= $this->markupMenuLink('about',     '',             __('About This Application'));
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
    public function markupMenuLink($op=false, $utility=false, $text='', $title=false)
    {
        // Check if this link is a database-requiring utility.
        $dba_reqd = (in_array($utility, $this->getAllDatabaseUtilities())) ? true : false;
        // Set an asterisk for use when dealing with database-requiring utilities.
        $asterisk = ($dba_reqd) ? '*' : null;
        // Ensure alt/title text is set and cast.
        $title = ($title) ? (string)$title : (string)$text;
        // Assign the base link target.
        $action = $this->getBaseScript();
        // Set style of selected links based on $op's presence.
        $style = (empty($this->operation)) ? 'class="selected" ' : null;
        // LINK RETURN POINT: Link is disabled due to incompatibility.
        // -----
        // Return a dimmed link when incompatibility exists.
        if (!$this->appCompatible()) {
            return '<a '.$style.'href="'.$action.'"><del title="'. $title.'">'.$text.$asterisk.'</del></a>';
        }
        // LINK RETURN POINT: Main un-argumented link
        // -----
        // If no operation present (ie, Main) return link here.
        if (!$op) {
            return '<a '.$style.'href="'.$action.'" title="'. $title.'">'.$text.'</a>';
        }
        // Attach operation to action string.
        $action .= '?op='.$op;
        // Re-assign the selected style based on $op.
        $style = ($op === $this->operation) ? 'class="selected"' : null;
        // LINK RETURN POINT: Link has $op arg only (ie, about, phpinfo).
        // -----
        if (!$utility) {
            return '<a '.$style.'href="'.$action.'" title="'. $title.'">'.$text.'</a>';
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
                return '<a '.$style.'href="'.$action.'"><del title="'. $title.'">'.$text.$asterisk.'</del></a>';
            }
            // Return a regular link with $op and $utility args if the database connection exists.
            return '<a '.$style.'href="'.$action.'" title="'. $title.'">'.$text.$asterisk.'</a>';
        }
        // LINK RETURN POINT: Link with $op and $utility, requires no database access. (ie, dba credential encoding utility.)
        return '<a '.$style.'href="'.$action.'" title="'. $title.'">'.$text.'</a>';
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
                $content .= __('<strong>THIS APPLICATION IS INCOMPATIBLE WITH YOUR SITE<br />This application works only with Zikula 1.x+.<br />When stil using PostNuke .764 consider the <a href="http://community.zikula.org/Downloads-req-viewdownload-cid-7.htm" title="PostNuke Swiss Army Knife">PostNuke Swiss Army Knife</a> (PSAK) utility.').'</div>'."\n\n";
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
            $content .= __('Invalid Operation').'</div>'."\n\n";
            return $content;
        }

        // Get and contain the title of the utility.
        $content .= '        <div id="page_title" title="'.$this->getTitle().'">'.$this->getTitle().'</div>'."\n\n";
        // Check if this particular utility requires db access.
        if (in_array($this->utility, $this->getAllDatabaseUtilities())) {
            // If so, check if database is ready.
            if (!$this->dbEnabled) {
                // If not, set descriptive message, close container & return; go no further.
                $content .= '        <div id="explain_disabled">Database required.</div>'."\n\n";
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
                    $content .= '        <div id="explain">'.__('Invalid Utility').'</div>'."\n\n";
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
        $search  = '<div><strong class="blue">'.__('Still need help?').'</strong> '.__('Search the <em>entire</em> Zikula site here!').'</div>'."\n\n";
        $search .= '<form method="post" action="http://community.zikula.org/index.php" style="display:inline;">
                    <div>
                        <img src="images/icons/extrasmall/search.png" style="float:left;margin:2px 5px 0 0;" alt="search" />
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
                        <input type="submit" id="search_b" value="'.__('SEARCH').'" title="Search" />
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
        $footer .= '<a href="http://code.zikula.org/core" title="'.'Zikula Recovery Console'.' v.'.self::VERSION.'">'.'Zikula Recovery Console'.' v.'.self::VERSION.'</a>';
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
            $markup .= '<strong>'.__('ERROR').'</strong><br />'."\n";
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
            $markup .= '<strong>'.__('STATUS').'</strong><br />'."\n";
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
            return __('Configuration Overview');
        }
        // Array of titles, keyed by operations.
        $titles = array('about'         => __('About This Application'),
                        'theme'         => __('Theme Recovery'),
                        'permission'    => __('Permission Recovery'),
                        'block'         => __('Block Recovery'),
                        'password'      => __('Password Reset'),
                        'tempdir'       => __('Rebuild Temp Directory'),
                        'outputfilter'  => __('Reset Output Filter'),
                        'phpids'        => __('Disable PHPIDS'),
                        'phpinfo'       => __('PHP Information'),
                        'site'          => __('Disabled Site Recovery'),
                        );
        // Return title based on operation if utility not present.
        if (!$this->utility) {
            return $titles[$this->operation];
        }
        // Utility present, return title based on utility.
        return $titles[$this->utility];
    }
    // Get any explanatory texts.
    public function getExplanation()
    {
        // Array of explanatoty defines, keyed by operations.
        $explanations = array('theme'         => __('<strong>INSTRUCTIONS:</strong> Use this utility to recover from theme-related fatal errors or to reset user-specified themes.'),
                              'permission'    => __('<strong>INSTRUCTIONS:</strong> Use this utility to reset your site permissions to the default state that was set when you originally installed the site.  Carefully review the chart below as an example of how your permissions will be setup after running this utility.'),
                              'block'         => __('<strong>INSTRUCTIONS:</strong> Use this utility to disable or delete blocks that you believe are causing issues for your site. Blocks that you disable can still be accessed by the system, but a deleted block is gone for good; double-check your choices before running this utility.  If there are no blocks present on your site, this utility will be disabled.'),
                              'site'          => __('<strong>INSTRUCTIONS:</strong> Use this utility to turn on your previously disabled site.'),
                              'password'      => __('<strong>INSTRUCTIONS:</strong> Use this utility to reset your admin (or other) password.'),
                              'tempdir'       => __('<strong>INSTRUCTIONS:</strong> Use this utility to rebuild your temp directory in case you accidentally deleted it or it is corrupted.'),
                              'outputfilter'  => __('<strong>INSTRUCTIONS:</strong> Use this utility to reset the output filter to \'internal\'. Useful if there is something wrong with the output filters in use (eg. HTMLPurifier).'),
                              'phpids'        => __('<strong>INSTRUCTIONS:</strong> Use this utility to disable PHPIDS. Useful if your settings in PHPIDS crashed the entire system.'),
                              'about'         => __('About')
                                                .__('<strong>VERSION</strong><br />This is <strong>Version '.self::VERSION.'</strong> of the <strong>Zikula Recovery Console</strong><br /><br />')
                                                .__('<strong>LICENSE</strong><br /><a href="http://www.gnu.org/copyleft/gpl.html" title="General Public License">General Public License</a><br /><br />')
                                                .__('<strong>CREDITS</strong><br />Maintained and enhanced by the Zikula CoreDev team. Originally developed by <a href="http://www.alarconcepts.com/" title="John Alarcon">John Alarcon</a>.  Greatly inspired by the ideas and work of <a href="http://www.snowjournal.com" title="Christopher S. Bradford">Christopher S. Bradford</a> and the additional supportive efforts of <a href="http://users.tpg.com.au/staer/" title="Martin Andersen">Martin Andersen</a>, <a href="http://www.landseer-stuttgart.de/" title="Frank Schummertz">Frank Schummertz</a>, <a href="http://pahlscomputers.com/" title="David Pahl">David Pahl</a> and <a href="http://www.itbegins.co.uk/" title="Simon Birtwistle">Simon Birtwistle</a>. Thanks guys!'),
                              );
        // Initialization.
        $explanation = '';
        // If no op or utility, assume main explanation is needed; return such.
        if (!$this->operation && !$this->utility) {
            return $explanation = __('The information shown below reflects the current configuration settings detected by the Zikula Recovery Console. Using the navigation at left, make use of the various site recovery utilities available. If the Zikula Recovery Console cannot resolve the issues your site is experiencing, try the search box at the bottom of any page to search the <a href="http://community.zikula.org/index.php?module=Forum" title="Zikula Support Forum">Zikula Support Forum</a> for answers.');
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
        $setting = '<strong>'.__('Current Setting:').'</strong> ';
        // Switch to get proper text.
        switch($this->utility) {
            case 'theme':
                $setting .= $this->siteTheme;
                break;
            case 'permission':
                $setting .= __('Nothing To Report');
                break;
            case 'block':
                $setting .= (count($this->blocks) == 0) ? __('Blocks not found') : __('Blocks Are Outlined Below');
                break;
            case 'site':
                $setting .= ($this->siteInactive == false) ? __('Site enabled') : __('Site Is Off/Disabled');
                break;
            case 'password':
                $setting .= __('Nothing To Report');
                break;
            case 'tempdir':
                $setting .= __f('Your temporary directory (based on your configuration file) is <strong>%s</strong>', $this->tempdir);
                break;
            case 'outputfilter':
                $outputfilter = System::getVar('outputfilter');
                if ($outputfilter == 0) {
                    $setting .= 'Your current output filter is set to internal mechanism';
                } else {
                    if ($outputfilter == 1) {
                        $setting .= 'Your current output filter is set to HTML Purifier + internal mechanism';
                    }
                    else {
                        $setting .= 'Unable to determine your current output filter setting';
                    }
                }
                break;
            case 'phpids':
                $phpids = System::getVar('useids');
                $setting .= ($phpids == 0) ? __('PHPIDS is disabled') : __('PHPIDS is enabled');
                break;
            default:
                $setting .= __('Nothing To Report');
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
            return $this->getMarkedUpOverview();
        }
        // PHPinfo needs no form, return marked-up PHPinfo instead.
        if ($this->operation === 'phpinfo') {
            return $this->getMarkedUpPHPInfo();
        }

        // Check if this utility should be disabled.
        if ($this->getUtilityState() == false) {
            // Start off with an explanatory text.
            $form = __('This utility is now disabled.');
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
        $form = '            <form method="post" action="'.$this->getBaseScript().'?op='.$this->operation.'&amp;utility='.$this->utility.'" enctype="application/x-www-form-urlencoded">'."\n\n";
        // If any additional inputs/markup are available for this utility, get them.
        if (method_exists($this, $method='getUtilityInputs'.$this->utility)) {
            $form .= $this->$method();
        }
        // Confirmation and submit inputs.
        $form .= '                <div class="row"><div class="row_left"><label for="confirm">'.__('Click To Confirm').'</label></div><div class="row_right"><input id="confirm" type="checkbox" name="confirm" value="1" /></div></div>'."\n\n";
        $form .= '                <div class="row"><div class="row_left"><label for="submit">&nbsp;</label></div><div class="row_right"><input class="submit" type="submit" name="submit" id="submit" value="'.__('Run Utility').'" /></div></div>'."\n\n";
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
                    $this->setRecoveryOutput(__('There are no blocks existing on your site which means that this utility can provide no assistance.'));
                    $state = false;
                }
                break;
            case 'site':
                // if site is enabled, disable this utility
                if (System::getVar('siteoff') == 0) {
                    $this->setRecoveryOutput(__('Your site is already enabled.'));
                    $state = false;
                }
                break;
             case 'tempdir':
                if (isset($this->INPUT['confirm']) && $this->INPUT['confirm'] == 1) {
                    $this->setRecoveryOutput(__('You have to reload the utility to see the current status of the temp directory.'));
                    $state = false;
                } elseif ($this->tempdirexists && $this->tempdirsubsfailed == 0) {
                    $output  = "<br /><br />\n";
                    $output .= __('Your temporary directory seems to exist. The status of the subdirectories is as follows:')."\n";
                    $output .= "<br /><br />\n";

                    foreach($this->tempdirsubs as $dir => $status) {
                        $output .= '<img src="images/icons/extrasmall/button_ok.png" alt="'.__('directory status').'" /> '.$dir."\n";
                        $output .= "<br />\n";
                    }

                    $output .= "<br />\n";
                    $output .= __('There is nothing wrong with your temporary directory so you don\'t need to rebuild it.');

                    $this->setRecoveryOutput($output);
                    $state = false;
                }
                break;
            case 'outputfilter':
                // if outputfilter is internal, disable this utility
                if (System::getVar('outputfilter') == 0) {
                    $this->setRecoveryOutput(__('Output filter is already set to internal mechanism.'));
                    $state = false;
                }
                break;
            case 'phpids':
                // if PHPIDS is disabled, disable this utility
                if (System::getVar('useids') == 0) {
                    $this->setRecoveryOutput(__('PHPIDS is already disabled.'));
                    $state = false;
                }
                break;
            default:
                break;
        }
        // Return the state of this utility.
        return $state;
    }

    // enable site
    public function getUtilityInputsSite()
    {
        return '<p>'.__('Set the following checkbox to re-enable your site.').'</p>';
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
            $label =__('Core Themes');
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
        $form  = '                <div class="row"><div class="row_left"><label for="themes">'.__('Available Themes').'</label></div><div class="row_right">'.$selector.'</div></div>'."\n";
        $form .= '                <div class="row"><div class="row_left"><label for="resetusers">'.__('Reset User Themes').'</label></div><div class="row_right"><input id="resetusers" type="checkbox" name="resetusers" value="1"'.(($this->INPUT['resetusers'] && $this->getErrors()) ? ' checked="checked"' : null).'  /></div></div>'."\n";
        return $form;
    }

    // Get additional inputs for permission recovery.
    public function getUtilityInputsPermission()
    {
        // Cheating a little; there are no actual inputs here.
        $form  = '<pre style="font-size:7.25pt;">';
        $form .= '<strong style="font-size:9pt;">'.__('Default Permissions').'</strong>'."\n";
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
        $form .= '<th>'.__('BID').'</th>'."\n\n";
        $form .= '<th>'.__('Key').'</th>'."\n\n";
        $form .= '<th width="100%">'.__('Title').'</th>'."\n\n";
        $form .= '<th>'.__('State').'</th>'."\n\n";
        $form .= '<th>'.__('MID').'</th>'."\n\n";
        $form .= '<th>'.__('Make No Changes').'</th>'."\n\n";
        $form .= '<th>'.__('Disable Block').'</th>'."\n\n";
        $form .= '<th>'.__('Delete Block').'</th>'."\n\n";
        $form .= '</tr>'."\n\n";
        $row=0;
        foreach ($this->blocks as $block) {
            $class = ($row++ & 1) ? ' style="background:#fafafa;"' : null;
            $form .= '<tr'.$class.'>'."\n\n";
            $form .= '<td class="center">'.$block['bid'].'</td>'."\n\n";
            $form .= '<td>'.$block['bkey'].'</td>'."\n\n";
            $form .= '<td>'.$block['title'].'</td>'."\n\n";
            $form .= '<td class="center">'.(($block['active']) ? '<img src="images/icons/extrasmall/greenled.png" alt="'.$block['title'].' :: '.__('active').'" title="'.$block['title'].' :: '.__('active').'" />' : '<img src="images/icons/extrasmall/yellowled.png" alt="'.$block['title'].' :: '.__('inactive').'" title="'.$block['title'].' :: '.__('inactive').'" />').'</td>'."\n\n";
            $form .= '<td class="center">'.$block['mid'].'</td>'."\n\n";
            $form .= '<td class="center"><input type="radio" name="blocks['.$block['bid'].']" value="0"'.((empty($this->INPUT['blocks'][$block['bid']])) ? ' checked="checked"' : null).' /></td>'."\n\n";
            if ($block['active'] != 0) {
                $form .= '<td class="center"><input type="radio" name="blocks['.$block['bid'].']" value="1"'.(($this->INPUT['blocks'][$block['bid']]==1) ? ' checked="checked"' : null).' /></td>'."\n\n";
            } else {
                $form .= '<td class="center">'.__('N/A').'</td>';
            }
            $form .= '<td class="center"><input type="radio" name="blocks['.$block['bid'].']" value="2"'.(($this->INPUT['blocks'][$block['bid']]==2) ? ' checked="checked"' : null).' /></td>'."\n\n";
            $form .= '</tr>'."\n\n";
        }
        $form .= '</table><br />'."\n\n";
        return $form;
    }

    // Get additional inputs for password reset.
    public function getUtilityInputsPassword()
    {
        $form   = '                <div class="row"><div class="row_left"><label for="zuname">'.__('Username').'</label></div><div class="row_right"><input type="text" id="zuname" name="zuname" value="admin" /></div></div>'."\n";
        $form  .= '                <div class="row"><div class="row_left"><label for="zpass1">'.__('New password').'</label></div><div class="row_right"><input type="password" id="zpass1" name="zpass1" /></div></div>'."\n";
        $form  .= '                <div class="row"><div class="row_left"><label for="zpass2">'.__('New password again (for verification)').'</label></div><div class="row_right"><input type="password" id="zpass2" name="zpass2" /></div></div>'."\n";

        return $form;
    }

     // Get additional inputs for temporary directory rebuild.
    public function getUtilityInputsTempdir()
    {
        if ($this->tempdirexists == false) {
            $form  = __('Your temporary directory does not seem to exist. It will be built from scratch')."\n";
        } else {
            $form  = __('Your temporary directory seems to exist. The status of the subdirectories is as follows:')."\n";
            $form .= "<br /><br />\n";

            foreach($this->tempdirsubs as $dir => $status) {
                if ($status == 1) {
                    $icon = 'button_ok.png';
                } else {
                    $icon = 'button_cancel.png';
                }
                $form .= '<img src="images/icons/extrasmall/'.$icon.'" alt="'.__('directory status').'" /> '.$dir."\n";
                $form .= "<br />\n";
            }

            $form .= "<br />\n";

            $form .= _fn('%s directory was not found. Please use the utility to create the deleted directory', '%s directories were not found. Please use the utility to create the deleted directories', $this->tempdirsubsfailed, $this->tempdirsubsfailed);
        }

        $form .= "<br /><br />\n";
       
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
        $form .= '[ <a href="'.$this->getBaseScript().'?op=phpinfo&amp;view=0" title="'.__('PHP Version').'">'.__('PHP Version').'</a> ]';
        $form .= '[ <a href="'.$this->getBaseScript().'?op=phpinfo&amp;view=4" title="'.__('PHP Core').'">'.__('PHP Core').'</a> ]';
        $form .= '[ <a href="'.$this->getBaseScript().'?op=phpinfo&amp;view=32" title="'.__('PHP Variables').'">'.__('PHP Variables').'</a> ]';
        $form .= '[ <a href="'.$this->getBaseScript().'?op=phpinfo&amp;view=64" title="'.__('PHP License').'">'.__('PHP License').'</a> ]'."\n";
        $form .= '<br />';
        $form .= '[ <a href="'.$this->getBaseScript().'?op=phpinfo&amp;view=16" title="'.__('PHP Environment').'">'.__('PHP Environment').'</a> ]';
        $form .= '[ <a href="'.$this->getBaseScript().'?op=phpinfo&amp;view=8" title="'.__('Apache Environment').'">'.__('Apache Environment').'</a> ]';
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
        $form  = '            <div class="row"><div class="row_left">'.__('Core Version').'</div><div class="row_right">'.$this->siteCodebase.' '.$this->siteVersion.'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'.__('Config File').'</div><div class="row_right">'.$this->siteConfigFile.'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'.__('Site Status').'</div><div class="row_right">'.(($this->siteInactive) ? '<img src="images/icons/extrasmall/redled.png" />'.__('Off/Disabled') :'<img src="images/icons/extrasmall/greenled.png" />'.__('On/Enabled')).'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'.__('Database').'</div><div class="row_right">'.(($this->dbEnabled) ? '<img src="images/icons/extrasmall/greenled.png" />'.__('Connected') : '<img src="images/icons/extrasmall/redled.png" />'.__('Not Connected')).'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'.__('Site Language').'</div><div class="row_right">'.$this->siteLang.'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'.__('Detected Themes').'</div><div class="row_right">'.$this->getMarkedUpOverviewThemeList().'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'.__('Detected Modules').'</div><div class="row_right">'.$this->getMarkedUpOverviewModuleList().'</div></div>'."\n\n";
        $form .= '            <div class="row"><div class="row_left">'.__('Detected Blocks').'</div><div class="row_right">'.$this->getMarkedUpOverviewBlockList().'</div></div>'."\n\n";
        $form .= '            <div style="clear:both;"></div>'."\n\n";
        return $form;
    }
    // Get marked up theme list for overview page.
    public function getMarkedUpOverviewThemeList()
    {
        // Initialization.
        $list = '';
        // Clause for when no themes came back.
        if (!isset($this->themes['corethemes'])) {
            return $list = __('None Detected');
        }

        if (!empty($this->themes['corethemes'])) {
            $list .= '<strong>'.__('Core Themes').'</strong>';
            $list .= $this->getMarkedUpOverviewThemeListReal($this->themes['corethemes']);
        }

        return $list;
    }

    public function getMarkedUpOverviewThemeListReal($themesarray)
    {
        // Looping through core themes.
        $list = '<ul>';
        foreach ($themesarray as $theme) {
            if ($theme['name'] === $this->siteTheme) {
                $list .= '<li><img src="images/icons/extrasmall/greenled.png" alt="'.$theme['name'].' :: '.__('active').'" title="'.$theme['name'].' :: '.__('set as default').'" />';
            } else if ($theme['state'] == 1){
                $list .= '<li><img src="images/icons/extrasmall/yellowled.png" alt="'.$theme['name'].' :: '.__('inactive').'" title="'.$theme['name'].' :: '.__('inactive').'" />';
            } else {
                $list .= '<li><img src="images/icons/extrasmall/redled.png" alt="'.$theme['name'].' :: '.__('uninitialized').'" title="'.$theme['name'].' :: '.__('uninitialized').'" />';
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
            return $list = __('None Detected');
        }

        $list .= '<strong>'.__('System Modules').'</strong>';
        $list .= $this->getMarkedUpOverviewModuleListReal($this->modules['sys_mods']);
        $list .= '<strong>'.__('Value Added Modules').'</strong>';
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
                    case ModUtil::STATE_UNINITIALISED:
                        $list .= '<img src="images/icons/extrasmall/redled.png" alt="'.$mod['name'].' :: '.__('uninitialized').'" title="'.$mod['name'].' :: '.__('uninitialized').'" />';
                        break;
                    case ModUtil::STATE_INACTIVE:
                        $list .= '<img src="images/icons/extrasmall/yellowled.png" alt="'.$mod['name'].' :: '.__('inactive').'" title="'.$mod['name'].' :: '.__('inactive').'" />';
                        break;
                    case ModUtil::STATE_ACTIVE:
                        $list .= '<img src="images/icons/extrasmall/greenled.png" alt="'.$mod['name'].' :: '.__('active').'" title="'.$mod['name'].' :: '.__('active').'" />';
                        break;
                    case ModUtil::STATE_MISSING:
                        $list .= '<img src="images/icons/extrasmall/14_layer_deletelayer.png" alt="'.$mod['name'].' :: '.__('files missing').'" title="'.$mod['name'].' :: '.__('files missing').'" />';
                        break;
                    case ModUtil::STATE_UPGRADED:
                        $list .= '<img src="images/icons/extrasmall/agt_update-product.png" alt="'.$mod['name'].' :: '.__('upgraded').'" title="'.$mod['name'].' :: '.__('upgraded').'" />';
                        break;
                    case ModUtil::STATE_INVALID:
                    default:
                        $list .= '<img src="images/icons/extrasmall/14_layer_deletelayer.png" alt="'.$mod['name'].' :: '.__('invalid').'" title="'.$mod['name'].' :: '.__('invalid').'" />';
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
            return __('None Detected');
        }
        $list = '<ul>';
        // Loop through all blocks.
        foreach ($this->blocks as $block) {
            // Append module-state image.
            if ($block['active'] == 1) {
                $list .= '<li><img src="images/icons/extrasmall/greenled.png" alt="'.$block['title'].' :: '.__('active').'" title="'.$block['title'].' :: '.__('active').'" />';
            } else {
                $list .= '<li><img src="images/icons/extrasmall/yellowled.png" alt="'.$block['title'].' :: '.__('inactive').'" title="'.$block['title'].' :: '.__('inactive').'" />';
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
            $this->setError(__('Invalid Utility'));
            $this->INPUT = false;
            return false;
        }
        // Check for confirmation value.
        if ($this->INPUT['confirm'] != 1) {
            $this->setError(__('Confirmation Required'));
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
            case 'tempdir':
                $success = $this->processRecoveryTempdir();
                break;
            case 'outputfilter':
                $success = $this->processRecoveryOutputFilter();
                break;
             case 'phpids':
                $success = $this->processRecoveryPHPIDS();
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
            $this->setError(__('Error Enabling Site'));
            return false;
        }
        $this->siteInactive = false;
        // Set a status message.
        $this->setStatus(__('Recovery Successful'));
        return true;
    }
    
    // Process recovery from theme errors.
    public function processRecoveryTheme()
    {
        // Get the new theme chosen.
        $theme = $this->INPUT['theme'];
        // If input theme is not a coretheme validity fails.
        if (!array_key_exists($theme, $this->themes['corethemes'])) {
            $this->setError(__('Invalid Theme Name'));
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
        $this->setStatus(__('Recovery Successful'));
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
        $this->setStatus(__('Recovery Successful'));
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
            $this->setStatus(__('No Changes Were Made'));
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
                    $this->setError(__('Block Not Disabled').': '.$block['title']);
                    continue;
                }
                // Set status message.
                $this->setStatus(__('Block Disabled').': '.$block['title']);
            } else if ($action == 2) { // 2 = delete block
                // Attempt block deletion.
                if (!$this->deleteBlock($bid)) {
                    // Set error.
                    $this->setError(__('Block Not Deleted').': '.$block['title']);
                    continue;
                }
                // Set status message.
                $this->setStatus(__('Block Deleted').': '.$block['title']);
            }
        }
        // Return false if any errors.
        if (!$this->getErrors()) {
            return false;
        }
        // Set a status message.
        $this->setStatus(__('Recovery Successful'));
        // Return true for success.
        return true;
    }

    // Process reset password.
    public function processRecoveryPassword()
    {
        // get variables
        $username = $this->INPUT['zuname'];
        $password1 = $this->INPUT['zpass1'];
        $password2 = $this->INPUT['zpass2'];

        // check that username is not empty
        if (empty($username)) {
            $this->setError(__('Username cannot be empty'));
            return false;
        }

        // check that password is not empty
        if (empty($password1)) {
            $this->setError(__('Password cannot be empty'));
            return false;
        }

        // check that passwords match
        if ($password1 != $password2) {
            $this->setError(__('Passwords do not match'));
            return false;
        }

         // check that username is not the anonymous one
        $anonymous = ModUtil::getVar('Users', 'anonymous');
        if ($username == $anonymous || $username == strtolower($anonymous)) {
            $this->setError(__('You cannot change the password for the anonymous user. Please provide the username of a valid user'));
            return false;
        }

        $table = DBUtil::getTables();
        $userstable  = $table['users'];
        $userscolumn = $table['users_column'];

        // check that username exists
        $uid = DBUtil::selectField('users', 'uid', $userscolumn['uname']."='".$username."'");
        if (!$uid) {
            $this->setError(__('The username you supplied is not valid'));
            return false;
        }
        
        // hash the password and check if it is valid
        $password = UserUtil::getHashedPassword($password1);
        if (!$password) {
            $this->setError(__('The password you supplied is not valid'));
            return false;
        }

        // update the password
        // create the object
        $obj = array('uid' => $uid, 'pass' => $password);

        // perform update
        if (!DBUtil::updateObject($obj, 'users', '', 'uid')) {
            $this->setError(__('Error resetting the password'));
            return false;
        }

        // Set a status message.
        $this->setStatus(__('The password was successfully reset'));

        // Recovery successful.
        return true;
    }

    // Process temp directory rebuild
    public function processRecoveryTempdir()
    {
        // some checks
        if (!isset($this->tempdir)) {
            $this->setError(__('Temporary directory is not set'));
            return false;
        }

        if (empty($this->tempdir)) {
            $this->setError(__('Temporary directory cannot be empty'));
            return false;
        }

        $dir_errors = array();
        
        // recreate only the subdirectories that are missing
        if ($this->tempdirexists) {
            foreach($this->tempdirsubs as $dir => $status) {
                if ($status == 0) {
                    $result = mkdir($this->tempdir.'/'.$dir, null, true);
                    if ($result == false) {
                        array_push($dir_errors, $dir);
                    }
                }
            }
        }
       // create all subdirectories
        else {
            foreach($this->tempdirsubs as $dir => $status) {
                $result = mkdir($this->tempdir.'/'.$dir, null, true);
                if ($result == false) {
                    array_push($dir_errors, $dir);
                }
            }
        }

        if (count($dir_errors) > 0) {
            $this->setError(__f('Error creating temp subdirectories [%s]', implode(",", $dir_errors)));
            return false;
        }

        // create htaccess only if needed
        if (!$this->tempdirexists) {
            $htaccess_file  = 'SetEnvIf Request_URI "\.css$" object_is_css=css'."\n";
            $htaccess_file .= 'SetEnvIf Request_URI "\.js$" object_is_js=js'."\n";
            $htaccess_file .= 'Order deny,allow'."\n";
            $htaccess_file .= 'Deny from all'."\n";
            $htaccess_file .= 'Allow from env=object_is_css'."\n";
            $htaccess_file .= 'Allow from env=object_is_js'."\n";

            $result = FileUtil::writeFile($this->tempdir.'/.htaccess', $htaccess_file);
            if ($result === false) {
                $this->setError(__f('There was a problem creating .htaccess file. You will have to download it yourself from the <a href="%s">CoZi</a> and place it inside your temp directory', 'https://code.zikula.org/svn/core/branches/zikula-1.3/src/ztemp/.htaccess'));
                return false;
            }
        }
        
        // Set a status message.
        $this->setStatus(__('The temp directory was successfully rebuilt'));

        // Recovery successful.
        return true;
    }

    // Process output filter recovery
    public function processRecoveryOutputFilter()
    {
        if (!System::setVar('outputfilter', '0')) {
            $this->setError(__('Error setting output filter to internal mechanism'));
            return false;
        }
        
        // Set a status message.
        $this->setStatus(__('Recovery Successful'));
        return true;
    }

     // Process PHPIDS disable
    public function processRecoveryPHPIDS()
    {
        if (!System::setVar('useids', false)) {
            $this->setError(__('Error disabling PHPIDS'));
            return false;
        }

        // Set a status message.
        $this->setStatus(__('Recovery Successful'));
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
        return $this->statusMessages;
    }
    // Return all error messages.
    public function getErrors()
    {
        return $this->errorMessages;
    }
    // Return any utility output texts.
    public function getRecoveryOutput()
    {
        return $this->recoveryOutput;
    }

    // Return an array of all possible operations.
    public function getAllOperations()
    {
        return array('about', 'recover', 'phpinfo');
    }
    // Return an array of all possible utilities.
    public function getAllUtilities()
    {
        return array('theme', 'permission', 'block', 'site', 'password', 'tempdir', 'outputfilter', 'phpids');
    }
    // Return an array of database-requiring utilities.
    public function getAllDatabaseUtilities()
    {
        return array('theme', 'permission', 'block', 'site', 'password');
    }

    // Disable a sideblock.
    public function disableBlock($bid)
    {
        // Ensure that $bid is 1 or higher.
        if ($bid < 1) {
            $this->setError(__('Block ID Invalid'));
            return false;
        }

        // Verify that block information was obtained.
        if (!BlockUtil::getBlockInfo($bid)) {
            $this->setError(__('No Such Block Exists'));
            return false;
        }

        // To be sure none more than active-state is changed.
        $obj = array('bid'=>$bid, 'active'=>0);

        // Attempt to disable the block.
        if (!DBUtil::updateObject ($obj, 'blocks', '', 'bid')) {
            $this->setError(__('Block Not Disabled'));
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
            $this->setError(__('Block ID Invalid'));
            return false;
        }

        // Ensure block exists.
        if (!BlockUtil::getBlockInfo($bid)) {
            $this->setError(__('No Such Block Exists'));
            return false;
        }

        // Delete block placements for this block.
        if (!DBUtil::deleteObjectByID('block_placements', $bid, 'bid')) {
            $this->setError(__('Block Placements Not Removed'));
            return false;
        }

        // Delete the block itself.
        if (!DBUtil::deleteObjectByID ('blocks', $bid, 'bid')) {
            $this->setError(__('Block Not Deleted'));
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
            $this->setError(__('Error! Permission not deleted'));
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
            $this->setError(__('Error inserting permission'));
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
                $this->setError(__('Error Resetting User Themes'));
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
