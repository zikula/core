#!/usr/bin/php
<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula 1.2.x to 1.3.x migration script.
 *
 * Usage: php replace.php [/path/to/module_or_theme_or_file]
 */
class Replace
{
    private $_input;    
     
    /**
     * Construct function
     *
     * @param array $argv Input arguments.
     *
     * @return void
     */ 
    public function __construct($argv)
    {    
        $this->validateInputArgument($argv);
        $this->migrate();   
    }
    
    /**
     * Input argument validation
     *
     * @param array $argv Input arguments.
     *
     * @return void
     */ 
    public function validateInputArgument($argv)
    {    
        if (empty($argv[1])) {
            echo 'Usage: '.$argv[0].' [/path/to/module_or_theme_or_file]'.PHP_EOL;
            die();
        }

        $this->_input = $argv[1];
    }

    /**
     * Check if a file is a smarty template
     *
     * @param string $input Path to file.
     *
     * @return boolean True if it is template
     */ 
    public function isTemplate($input)
    {
        if (!is_file($input)) {
            return false;
        }
        
        $extenstion = substr($input, -4);                
        $templateExtensions = array('.tpl', '.htm', 'html');
        if (!in_array($extenstion, $templateExtensions)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if a file is a php file
     *
     * @param string $input Path to the file.
     *
     * @return boolean True if it is php file
     */ 
    public function isPHPFile($input)
    {
        if (!is_file($input)) {
            return false;
        }

        $extenstion = substr($input, -4);
                
        if ($extenstion != '.php') {
            return false;
        }

        return true;
    }

    /**
     * Migrate theme or module
     *
     * @param string $input Migration path.
     *
     * @return void
     */
    public function migrate()
    {
        if (is_dir($this->_input)) {
            $this->migrateModuleOrTheme($this->_input);
        } else if ($this->isPHPFile($this->_input)) {
            $this->migratePhpFile($this->_input);
        } else if($this->isTemplate($this->_input)) {
            $this->migrateTemplate($this->_input);
        } else {
            echo $this->_input.' is not a directory, a php file or a template file.'.PHP_EOL;
            die();
        }
    }
    
    /**
     * Migrate theme or module
     *
     * @param string $input Migration path.
     *
     * @return void
     */
    public function migrateModuleOrTheme($input)
    {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($input), RecursiveIteratorIterator::SELF_FIRST);
        foreach($objects as $name => $object) {
            if (strpos($name, '.svn/') === false && strpos($name, '.git/') === false) {
                if ($this->isPHPFile($name)) {
                    $this->migratePhpFile($name);
                } else if($this->isTemplate($name)) {
                    $this->migrateTemplate($name);
                }
            }
        }
    }
    
    /**
     * Migrate php file
     *
     * @param string $input Migration path.
     *
     * @return void
     */
    public function migratePhpFile($input)
    {
        $filename = realpath($input);
        echo "converting PHP file: $filename\n";
        $contents = file_get_contents($filename);
        $original = $contents;
        $contents = $this->replacePnApi($contents);
        if ($original === $contents) {
            echo "...No changes made to $filename \n";
        } else {
            file_put_contents($filename, $contents);
            echo "...Changes written to $filename \n";
        }
    }
  
    /**
     * Migrate smarty template
     *
     * @param string $input Path to the template file.
     *
     * @return void
     */   
    public function migrateTemplate($input)
    {
        $filename = realpath($input);
        echo "converting template: $filename\n";
        $content = file_get_contents($filename);
        $original = $content;
        $content = preg_replace_callback('`(<(script|style)[^>]*>)(.*?)(</\2>)`s', array($this, 'z_prefilter_add_literal_callback'), $content);
        $content = str_replace('<!--[', '{', $content);
        $content = str_replace(']-->', '}', $content);
        $content = str_replace('{pn', '{', $content);
        $content = str_replace('{/pn', '{/', $content);
        $content = str_replace('|pn', '|', $content);
        $content = str_replace('|date_format', '|dateformat', $content);
        $content = str_replace('|varprepfordisplay', '|safetext', $content);
        $content = str_replace('|varprephtmldisplay', '|safehtml', $content);
        if ($original === $content) {
            echo "...No changes made to $filename \n";
        } else {
            file_put_contents($filename, $content);
            echo "...Changes written to $filename \n";
        }   
    }

    /**
     * pnApi replacer
     *
     * @param string $content Content to migrate.
     *
     * @return string Migrated content
     */  
    public function replacePnApi($content)
    {
        $array = array(
        //    '__f' => '__f',
        //    '_fn' => '_fn',
        //    '__' => '__',
        //    '_n' => '_n',
        //    'no__' => 'no__',
        '_pntables' => '_tables',
        'pnrender::' => 'Zikula_View::',
        '& Zikula_View::' => 'Zikula_View::',
        'renderer::' => 'Zikula_View::',
        '& Zikula_View::' => 'Zikula_View::',
        'pnrender' => 'view',
        'pnconfiggetvar' => 'System::getVar',
        'pnconfigsetvar' => 'System::setVar',
        'pnconfigdelvar' => 'System::delVar',
        'pndbgetconn' => 'DBConnectionStack::getConnection*',
        'pndbgettables' => 'DBUtil::getTables',
        'pndbgettableprefix' => 'DBUtil::getTablePrefix',
        'System::dbGetConn' => 'DBConnectionStack::getConnection*',
        'System::dbGetTables' => 'DBUtil::getTables',
        'System::dbGetTablePrefix' => 'DBUtil::getTablePrefix',
        'pnstripslashes' => 'System::stripslashes',
        'pnvarvalidate' => 'System::varValidate',
        'pngetbaseuri' => 'System::getBaseUri',
        'pngetbaseurl' => 'System::getBaseUrl',
        'pngethomepageurl' => 'System::getHomepageUrl',
        'pnredirect' => 'System::redirect',
        'pnlocalreferer' => 'System::localReferer',
        'pnmail' => 'System::mail',
        'pnservergetvar' => 'System::serverGetVar',
        'pngethost' => 'System::getHost',
        'pngetcurrenturi' => 'System::getCurrentUri',
        'pnservergetprotocol' => 'System::serverGetProtocol',
        'pngetcurrenturl' => 'System::getCurrentUrl',
        'pnquerystringdecode' => 'System::queryStringDecode',
        'pnquerystringsetvar' => 'System::queryStringSetVar',
        'pnshutdown' => 'System::shutdown',
        'pn_exit' => 'z_exit',
        'pn_prayer' => 'z_prayer',
        'pnvarcleanfrominput' => 'FormUtil::getPassedValue',
        'pnphpversioncheck' => 'version_compare',
        'pnsecauthaction' => 'SecurityUtil::checkPermission*',
        'pnsecgetauthinfo' => 'SecurityUtil::getAuthInfo',
        'pnsecgetlevel' => 'SecurityUtil::getSecurityLevel',
        'pnsecgenauthkey' => 'SecurityUtil::generateAuthKey',
        'pnsecconfirmauthkey' => 'SecurityUtil::confirmAuthKey',
        'authorised' => 'SecurityUtil::checkPermission*',
        'pnsecaddschema' => 'SecurityUtil::registerPermissionSchema',
        'addinstanceschemainfo' => 'SecurityUtil::registerPermissionSchema',
        'accesslevelname' => 'SecurityUtil::accesslevelname',
        'accesslevelnames' => 'SecurityUtil::accesslevelnames',
        'getusertime' => 'getusertime*DEPRECATED*',
        'pngetstatusmsg' => 'LogUtil::getStatusMessages',
        'pnvarprepforos' => 'DataUtil::formatForOS',
        'pnvarprepfordisplay' => 'DataUtil::formatForDisplay',
        'pnvarprephtmldisplay' => 'DataUtil::formatForDisplayHTML',
        'pnvarprepforstore' => 'DataUtil::formatForStore',
        'pnsessiongetvar' => 'SessionUtil::getVar',
        'pnsessionsetvar' => 'SessionUtil::setVar',
        'pnsessiondelvar' => 'SessionUtil::delVar',
        'pnvarcensor' => 'pnvarcensor*DEPRECATED*',
        'pnmodinitcorevars' => 'ModUtil::initCoreVars',
        'pnmodvarexists' => 'ModUtil::hasVar',
        'pnmodgetvar' => 'ModUtil::getVar',
        'pnmodsetvar' => 'ModUtil::setVar',
        'pnmodsetvars' => 'ModUtil::setVars',
        'pnmoddelvar' => 'ModUtil::delVar',
        'pnmodgetidfromname' => 'ModUtil::getIdFromName',
        'pnmodgetinfo' => 'ModUtil::getInfo',
        'pnmodgetusermods' => 'ModUtil::getUserMods',
        'pnmodgetprofilemods' => 'ModUtil::getProfileMods',
        'pnmodgetmessagemods' => 'ModUtil::getMessageMods',
        'pnmodgetadminmods' => 'ModUtil::getAdminMods',
        'pnmodgettypemods' => 'ModUtil::getTypeMods',
        'pnmodgetallmods' => 'ModUtil::getAllMods',
        'pnmoddbinfoload' => 'ModUtil::dbInfoLoad',
        'pnmodload' => 'ModUtil::load',
        'pnmodapiload' => 'ModUtil::loadApi',
        'pnmodloadgeneric' => 'ModUtil::loadGeneric',
        'pnmodfunc' => 'ModUtil::func',
        'pnmodapifunc' => 'ModUtil::apiFunc',
        'pnmodfuncexec' => 'ModUtil::exec',
        'pnmodurl' => 'ModUtil::url',
        'pnmodavailable' => 'ModUtil::available',
        'pnmodgetname' => 'ModUtil::getName',
        'pnmodregisterhook' => 'ModUtil::registerHook',
        'pnmodunregisterhook' => 'ModUtil::unregisterHook',
        'pnmodcallhooks' => 'ModUtil::callHooks',
        'pnmodishooked' => 'ModUtil::isHooked',
        'pnmodgetbasedir' => 'ModUtil::getBaseDir',
        'pnmodgetmodstable' => 'ModUtil::getModsTable',
        'pnblockdisplayposition' => 'BlockUtil::displayPosition',
        'pnblockshow' => 'BlockUtil::show',
        'pnblockthemeblock' => 'BlockUtil::themeBlock',
        'pnblockload' => 'BlockUtil::load',
        'pnblockloadall' => 'BlockUtil::loadAll',
        'pnblockvarsfromcontent' => 'BlockUtil::varsFromContent',
        'pnblockvarstocontent' => 'BlockUtil::varsToContent',
        'pncheckuserblock' => 'BlockUtil::checkUserBlock',
        'pnblocksgetinfo' => 'BlockUtil::getBlocksInfo',
        'pnblockgetinfo' => 'BlockUtil::getBlockInfo',
        'pnblockgetinfobytitle' => 'BlockUtil::getInfoByTitle',
        'themesideblock' => 'BlockUtil::themesideblock',
        'pnuserlogin' => 'UserUtil::login',
        'pnuserloginhttp' => 'UserUtil::loginHttp',
        'pnuserlogout' => 'UserUtil::logout',
        'pnuserloggedin' => 'UserUtil::isLoggedIn',
        'pnusergetvars' => 'UserUtil::getVars',
        'pnusergetvar' => 'UserUtil::getVar',
        'pnusersetvar' => 'UserUtil::setVar',
        'pnusersetpassword' => 'UserUtil::setPassword',
        'pnuserdelvar' => 'UserUtil::delVar',
        'pnusergettheme' => 'UserUtil::getTheme',
        'pnusergetlang' => 'ZLanguage::getLanguageCode',
        'pnusergetall' => 'UserUtil::getAll',
        'pnusergetidfromname' => 'UserUtil::getIdFromName',
        'pnusergetidfromemail' => 'UserUtil::getIdFromEmail',
        'pnuserfieldalias' => 'UserUtil::fieldAlias',
        'pnthemeload' => 'ThemeUtil::load',
        'pnthemegetvar' => 'ThemeUtil::getVar',
        'pnthemegetallThemes' => 'ThemeUtil::getAllThemes',
        'pnthemelangload' => 'ThemeUtil::loadLanguage*DEPRECATED*',
        'pnthemegetidFromName' => 'ThemeUtil::getIDFromName',
        'pnthemegetinfo' => 'ThemeUtil::getInfo',
        'pnthemegetthemestable' => 'ThemeUtil::getThemesTable',
        'pncategoryregistry' => 'Categories_DBObject_Registry',
        'pncategoryregistryarray' => 'Categories_DBObject_RegistryArray',
        'pncategoryarray' => 'Categories_DBObject_CategoryArray',
        'pncategory' => 'Categories_DBObject_Category',
        'pnmodule_dependency_' => 'ModUtil::DEPENDENCY_',
        'PN_VERSION_NUM' => 'Zikula_Core::VERSION_NUM',
        'PN_VERSION_ID' => 'Zikula_Core::VERSION_ID',
        'PN_VERSION_SUB' => 'Zikula_Core::VERSION_SUB',
        'PN_CORE_' => 'Zikula_Core::STAGE_',
        'PNMODULE_STATE_' => 'ModUtil::STATE_',
        'MODULE_TYPE_' => 'ModUtil::TYPE_',
        'PNTHEME_FILTER_' => 'ThemeUtil::FILTER_',
        'PNTHEME_TYPE_' => 'ThemeUtil::TYPE_',
        'PNTHEME_STATE_' => 'ThemeUtil::STATE_',
        );
    
        $replaceArray = array_values($array);
        $searchArray = array();
        foreach ($array as $key => $value) {
            $searchArray[] = "#$key#i";
        }
        
        return preg_replace($searchArray, $replaceArray, $content);
    }

    /**
     * Literal callback function
     *
     * @param array $matches Matches array.
     *
     * @return string Migrated content
     */  
    function z_prefilter_add_literal_callback($matches)
    {
        $tagOpen = $matches[1];
        $script = $matches[3];
        $tagClose = $matches[4];
    
        $script = str_replace('<!--[', '{{', str_replace(']-->', '}}', $script));
    
        return $tagOpen . $script . $tagClose;
    }
}

$replace = new Replace($argv);