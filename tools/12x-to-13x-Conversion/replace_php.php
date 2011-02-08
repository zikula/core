<?php
if ($_SERVER['argc'] < 1) {
    die("Usage: php -f xcompile.php /full/path/to/file.php\n");
}
$filename = realpath($_SERVER['argv'][1]);

if (!file_exists($filename)) {
    die("file $filename does not exist");
}

echo "converting PHP file: $filename\n";
$contents = file_get_contents($filename);
$original = $contents;
$contents = replace_pnApi($contents);
if ($original === $contents) {
    echo "...No changes made to $filename \n";
    exit;
}
file_put_contents($filename, $contents);
echo "...Changes written to $filename \n";


function replace_pnApi($contents)
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
    
    return preg_replace($searchArray, $replaceArray, $contents);
}
