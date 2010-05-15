<?php
/**
 * Zikula Application Framework
 * @version $Id$
 * @license GNU/GPLv2 (or at your option any later version).
 * @copyright see NOTICE
 */

// backwards compatibility references
$GLOBALS['PNConfig'] = & $GLOBALS['ZConfig'];
$GLOBALS['PNRuntime'] = & $GLOBALS['ZRuntime'];

// start BC classes licensed as LGPLv2
class pnRender extends Renderer
{
}
class PNObject extends DBObject
{
    public function PNObject($init = null, $key = null, $field = null)
    {
        $this->DBObject($init, $key, $field);
    }
}

class PNObjectArray extends DBObjectArray
{
    public function PNObjectArray($init = null, $where = '')
    {
        $this->DBObjectArray($init, $where);
    }
}

// pnForm backward compatibility
class pnForm extends Form
{
    public $pnFormState;

    /**
     * List of included files required to recreate plugins (Smarty function.xxx.php files)
     * @internal
     */
    public $pnFormIncludes;

    /**
     * List of instantiated plugins
     * @internal
     */
    public $pnFormPlugins;

    /**
     * Stack with all instantiated blocks (push when starting block, pop when ending block)
     * @internal
     */
    public $pnFormBlockStack;

    /**
     * List of validators on page
     * @internal
     */
    public $pnFormValidators;

    /**
     * Flag indicating if validation has been done or not
     * @internal
     */
    public $pnFormValidationChecked;

    /**
     * Indicates whether page is valid or not
     * @internal
     */
    public $_pnFormIsValid;

    /**
     * Current ID count - used to assign automatic ID's to all items
     * @internal
     */
    public $pnFormIdCount;

    /**
     * Reference to the main user code event handler
     * @internal
     */
    public $pnFormEventHandler;

    /**
     * Error message has been set
     * @internal
     */
    public $pnFormErrorMsgSet;

    /**
     * Set to true if pnFormRedirect was called. Means no HTML output should be returned.
     * @internal
     */
    public $pnFormRedirected;

    public function __construct()
    {
        parent::__construct();
        $this->pnFormState = &$this->State;
        $this->pnFormIncludes = &$this->Includes;
        $this->pnFormPlugins = &$this->Plugins;
        $this->pnFormBlockStack = &$this->BlockStack;
        $this->pnFormValidators = &$this->Validators;
        $this->pnFormValidationChecked = &$this->ValidationChecked;
        $this->_pnFormIsValid = &$this->_IsValid;
        $this->pnFormIdCount = &$this->IdCount;
        $this->pnFormEventHandler = &$this->EventHandler;
        $this->pnFormErrorMsgSet = &$this->ErrorMsgSet;
        $this->pnFormRedirected = &$this->Redirected;
    }

    public function pnFormExecute($template, &$eventHandler)
    {
        return $this->Execute($template, &$eventHandler);
    }
    public function pnFormRegisterPlugin($pluginName, &$params, $isBlock = false)
    {
        return $this->RegisterPlugin($pluginName, &$params, $isBlock = false);
    }
    public function pnFormRegisterBlock($pluginName, &$params, &$content)
    {
        $this->RegisterBlock($pluginName, &$params, &$content);
    }
    public function pnFormRegisterBlockBegin($pluginName, &$params)
    {
        $this->RegisterBlockBegin($pluginName, &$params);
    }
    public function pnFormRegisterBlockEnd($pluginName, &$params, $content)
    {
        return $this->RegisterBlockEnd($pluginName, &$params, $content);
    }
    public function pnFormGetPluginId(&$params)
    {
        return $this->GetPluginId(&$params);
    }
    public function pnFormIsPostBack()
    {
        return $this->IsPostBack();
    }
    public function pnFormDie($msg)
    {
        $this->FormDie($msg);
    }
    public function pnFormTranslateForDisplay($txt, $doEncode = true)
    {
        return $this->TranslateForDisplay($txt, $doEncode = true);
    }
    public function pnFormAddValidator(&$validator)
    {
        $this->AddValidator(&$validator);
    }
    public function pnFormIsValid()
    {
        return $this->IsValid();
    }
    public function pnFormValidate()
    {
        $this->Validate();
    }
    public function pnFormClearValidation()
    {
        $this->ClearValidation();
    }
    public function pnFormSetState($region, $varName, &$varValue)
    {
        $this->SetState($region, $varName, &$varValue);
    }
    public function pnFormSetErrorMsg($msg)
    {
        return $this->SetErrorMsg($msg);
    }
    public function pnFormGetErrorMsg()
    {
        return $this->GetErrorMsg();
    }
    public function pnFormHasError()
    {
        return $this->HasError();
    }
    public function pnFormRegisterError($dummy)
    {
        return $this->RegisterError($dummy);
    }
    public function pnFormRedirect($url)
    {
        $this->Redirect($url);
    }
    public function pnFormGetPostBackEventReference($plugin, $commandName)
    {
        return $this->GetPostBackEventReference($plugin, $commandName);
    }
    public function pnFormRaiseEvent($eventHandlerName, $args)
    {
        return $this->RaiseEvent($eventHandlerName, $args);
    }
    public function pnFormInitializeIncludes()
    {
        $this->InitializeIncludes();
    }
    public function pnFormGetIncludesText()
    {
        return $this->GetIncludesText();
    }
    public function pnFormGetIncludesHTML()
    {
        return $this->GetIncludesHTML();
    }
    public function pnFormDecodeIncludes()
    {
        return $this->DecodeIncludes();
    }
    public function pnFormGetAuthKeyHTML()
    {
        return $this->GetAuthKeyHTML();
    }
    public function pnFormInitializeState()
    {
        $this->InitializeState();
    }
    public function pnFormGetStateText()
    {
        $this->GetStateText();
    }
    public function pnFormGetPluginState()
    {
        return $this->GetPluginState();
    }
    public function pnFormGetPluginState_rec($plugins)
    {
        return $this->GetPluginState_rec($plugins);
    }
    public function pnFormGetStateHTML()
    {
        return $this->GetStateHTML();
    }
    public function pnFormDecodeState()
    {
        $this->DecodeState();
    }
    public function pnFormDecodeEventHandler()
    {
        $this->DecodeEventHandler();
    }
    public function pnFormInitializePlugins()
    {
        return $this->InitializePlugins();
    }
    public function pnFormInitializePlugins_rec($plugins)
    {
        $this->InitializePlugins_rec($plugins);
    }
    public function pnFormDecodePlugins()
    {
        return $this->DecodePlugins();
    }
    public function pnFormDecodePlugins_rec($plugins)
    {
        $this->DecodePlugins_rec($plugins);
    }
    public function pnFormDecodePostBackEvent()
    {
        $this->DecodePostBackEvent();
    }
    public function pnFormDecodePostBackEvent_rec($plugins)
    {
        return $this->DecodePostBackEvent_rec($plugins);
    }
    public function pnFormPostRender()
    {
        return $this->PostRender();
    }
    public function pnFormPostRender_rec($plugins)
    {
        $this->PostRender_rec($plugins);
    }
    public function pnFormGetValues()
    {
        return $this->GetValues();
    }
    public function pnFormGetValues_rec($plugins, &$result)
    {
        $this->GetValues_rec($plugins, &$result);
    }
    public function pnFormSetValues(&$values, $group = null)
    {
        return $this->SetValues(&$values, $group);
    }
    public function pnFormSetValues2(&$values, $group = null, $plugins)
    {
        return $this->SetValues2(&$values, $group, $plugins);
    }
    public function pnFormSetValues_rec(&$values, $group, $plugins)
    {
        $this->SetValues_rec(&$values, $group, $plugins);
    }

}
class pnFormPlugin extends Form_Plugin
{
}
class pnFormStyledPlugin extends Form_StyledPlugin
{
}
class pnFormHandler extends Form_Handler
{
}
class pnFormBaseListSelector extends Form_Plugin_BaseListSelector
{
}
class pnFormButton extends Form_Plugin_Button
{
}
class pnFormCategoryCheckboxList extends Form_Plugin_CategoryCheckboxList
{
}
class pnFormCategorySelector extends Form_Plugin_CategorySelector
{
}
class pnFormCheckbox extends Form_Plugin_Checkbox
{
}
class pnFormCheckboxList extends Form_Plugin_CheckboxList
{
}
class pnFormContextMenu extends Form_Block_ContextMenu
{
}
class pnFormContextMenuItem extends Form_Plugin_ContextMenu_Item
{
}
class pnFormContextMenuReference extends Form_Plugin_ContextMenu_Reference
{
}
class pnFormContextMenuSeparator extends Form_Plugin_ContextMenu_Separator
{
}
class pnFormDateInput extends Form_Plugin_DateInput
{
}
class pnFormDropDownRelationlist extends Form_Plugin_DropdownRelationList
{
}
class pnFormDropdownList extends Form_Plugin_DropdownList
{
}
class pnFormEMailInput extends Form_Plugin_EmailInput
{
}
class pnFormErrorMessage extends Form_Plugin_ErrorMessage
{
}
class pnFormFloatInput extends Form_Plugin_FloatInput
{
}
class pnFormImageButton extends Form_Plugin_ImageButton
{
}
class pnFormIntInput extends Form_Plugin_IntInput
{
}
class pnFormLabel extends Form_Plugin_Label
{
}
class pnFormLanguageSelector extends Form_Plugin_LanguageSelector
{
}
class pnFormLinkButton extends Form_Plugin_LinkButton
{
}
class pnFormPostBackFunction extends Form_Plugin_PostbackFunction
{
}
class pnFormRadioButton extends Form_Plugin_RadioButton
{
}
class pnFormTabbedPanel extends Form_Block_TabbedPanel
{
}
class pnFormTabbedPanelSet extends Form_Block_TabbedPanelSet
{
}
class pnFormTextInput extends Form_Plugin_TextInput
{
}
class pnFormURLInput extends Form_Plugin_UrlInput
{
}
class pnFormUploadInput extends Form_Plugin_UploadInput
{
}
class pnFormValidationSummary extends Form_Plugin_ValidationSummary
{
}
class pnFormVolatile extends Form_Block_Volatile
{
}

// end BC classes


/**
 * @deprecated since 1.2
 * we now directly analyse the 2-digit language and country codes
 * Language list for auto detection of browser language
 */
function cnvlanguagelist()
{
    $cnvlang['KOI8-R'] = 'rus';
    $cnvlang['af'] = 'eng';
    $cnvlang['ar'] = 'ara';
    $cnvlang['ar-ae'] = 'ara';
    $cnvlang['ar-bh'] = 'ara';
    $cnvlang['ar-bh'] = 'ara';
    $cnvlang['ar-dj'] = 'ara';
    $cnvlang['ar-dz'] = 'ara';
    $cnvlang['ar-eg'] = 'ara';
    $cnvlang['ar-iq'] = 'ara';
    $cnvlang['ar-jo'] = 'ara';
    $cnvlang['ar-km'] = 'ara';
    $cnvlang['ar-kw'] = 'ara';
    $cnvlang['ar-lb'] = 'ara';
    $cnvlang['ar-ly'] = 'ara';
    $cnvlang['ar-ma'] = 'ara';
    $cnvlang['ar-mr'] = 'ara';
    $cnvlang['ar-om'] = 'ara';
    $cnvlang['ar-qa'] = 'ara';
    $cnvlang['ar-sa'] = 'ara';
    $cnvlang['ar-sd'] = 'ara';
    $cnvlang['ar-so'] = 'ara';
    $cnvlang['ar-sy'] = 'ara';
    $cnvlang['ar-tn'] = 'ara';
    $cnvlang['ar-ye'] = 'ara';
    $cnvlang['be'] = 'eng';
    $cnvlang['bg'] = 'bul';
    $cnvlang['bo'] = 'tib';
    $cnvlang['ca'] = 'eng';
    $cnvlang['cs'] = 'ces';
    $cnvlang['da'] = 'dan';
    $cnvlang['de'] = 'deu';
    $cnvlang['de-at'] = 'deu';
    $cnvlang['de-ch'] = 'deu';
    $cnvlang['de-de'] = 'deu';
    $cnvlang['de-li'] = 'deu';
    $cnvlang['de-lu'] = 'deu';
    $cnvlang['el'] = 'ell';
    $cnvlang['en'] = 'eng';
    $cnvlang['en-au'] = 'eng';
    $cnvlang['en-bz'] = 'eng';
    $cnvlang['en-ca'] = 'eng';
    $cnvlang['en-gb'] = 'eng';
    $cnvlang['en-ie'] = 'eng';
    $cnvlang['en-jm'] = 'eng';
    $cnvlang['en-nz'] = 'eng';
    $cnvlang['en-ph'] = 'eng';
    $cnvlang['en-tt'] = 'eng';
    $cnvlang['en-us'] = 'eng';
    $cnvlang['en-za'] = 'eng';
    $cnvlang['en-zw'] = 'eng';
    $cnvlang['es'] = 'spa';
    $cnvlang['es-ar'] = 'spa';
    $cnvlang['es-bo'] = 'spa';
    $cnvlang['es-cl'] = 'spa';
    $cnvlang['es-co'] = 'spa';
    $cnvlang['es-cr'] = 'spa';
    $cnvlang['es-do'] = 'spa';
    $cnvlang['es-ec'] = 'spa';
    $cnvlang['es-es'] = 'spa';
    $cnvlang['es-gt'] = 'spa';
    $cnvlang['es-hn'] = 'spa';
    $cnvlang['es-mx'] = 'spa';
    $cnvlang['es-ni'] = 'spa';
    $cnvlang['es-pa'] = 'spa';
    $cnvlang['es-pe'] = 'spa';
    $cnvlang['es-pr'] = 'spa';
    $cnvlang['es-py'] = 'spa';
    $cnvlang['es-sv'] = 'spa';
    $cnvlang['es-uy'] = 'spa';
    $cnvlang['es-ve'] = 'spa';
    $cnvlang['eu'] = 'eng';
    $cnvlang['fi'] = 'fin';
    $cnvlang['fo'] = 'eng';
    $cnvlang['fr'] = 'fra';
    $cnvlang['fr-be'] = 'fra';
    $cnvlang['fr-ca'] = 'fra';
    $cnvlang['fr-ch'] = 'fra';
    $cnvlang['fr-fr'] = 'fra';
    $cnvlang['fr-lu'] = 'fra';
    $cnvlang['fr-mc'] = 'fra';
    $cnvlang['ga'] = 'eng';
    $cnvlang['gd'] = 'eng';
    $cnvlang['gl'] = 'eng';
    $cnvlang['hr'] = 'cro';
    $cnvlang['hu'] = 'hun';
    $cnvlang['in'] = 'ind';
    $cnvlang['is'] = 'isl';
    $cnvlang['it'] = 'ita';
    $cnvlang['it-ch'] = 'ita';
    $cnvlang['it-it'] = 'ita';
    $cnvlang['ja'] = 'jpn';
    $cnvlang['ka'] = 'kat';
    $cnvlang['ko'] = 'kor';
    $cnvlang['mk'] = 'mkd';
    $cnvlang['nl'] = 'nld';
    $cnvlang['nl-be'] = 'nld';
    $cnvlang['nl-nl'] = 'nld';
    $cnvlang['no'] = 'nor';
    $cnvlang['pl'] = 'pol';
    $cnvlang['pt'] = 'por';
    $cnvlang['pt-br'] = 'por';
    $cnvlang['pt-pt'] = 'por';
    $cnvlang['ro'] = 'ron';
    $cnvlang['ro-mo'] = 'ron';
    $cnvlang['ro-ro'] = 'ron';
    $cnvlang['ru'] = 'rus';
    $cnvlang['ru-mo'] = 'rus';
    $cnvlang['ru-ru'] = 'rus';
    $cnvlang['sk'] = 'slv';
    $cnvlang['sl'] = 'slv';
    $cnvlang['sq'] = 'eng';
    $cnvlang['sr'] = 'eng';
    $cnvlang['sv'] = 'swe';
    $cnvlang['sv-fi'] = 'swe';
    $cnvlang['sv-se'] = 'swe';
    $cnvlang['th'] = 'tha';
    $cnvlang['tr'] = 'tur';
    $cnvlang['uk'] = 'ukr';
    $cnvlang['zh-cn'] = 'zho';
    $cnvlang['zh-tw'] = 'zho';

    return $cnvlang;
}

/**
 * clean user input
 *
 * Gets a global variable, cleaning it up to try to ensure that
 * hack attacks don't work
 *
 * @deprecated
 * @see FormUtil::getPassedValues
 * @param var $ name of variable to get
 * @param  $ ...
 *
 * @return mixed prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarCleanFromInput()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarCleanFromInput()',
        'FormUtil::getPassedValue()')), 'STRICT');

    $vars = func_get_args();
    $resarray = array();
    foreach ($vars as $var) {
        $resarray[] = FormUtil::getPassedValue($var);
    }

    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * Function that compares the current php version on the
 * system with the target one
 *
 * Deprecate function reverting to php detecion function
 *
 * @deprecated
 */
function pnPhpVersionCheck($vercheck = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnPhpVersionCheck()',
        'version_compare()')), 'STRICT');
    $minver = str_replace(".", "", $vercheck);
    $curver = str_replace(".", "", phpversion());

    if ($curver >= $minver) {
        return true;
    } else {
        return false;
    }
}

/**
 * see if a user is authorised to carry out a particular task
 *
 * @deprecated
 * @see SecurityUtil::checkPermission()
 * @param realm the realm under test
 * @param component the component under test
 * @param instance the instance under test
 * @param level the level of access required
 * @return bool true if authorised, false if not
 */
function pnSecAuthAction($testrealm, $testcomponent, $testinstance, $testlevel, $testuser = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecAuthAction()',
        'SecurityUtil::checkPermission()')), 'STRICT');

    return SecurityUtil::checkPermission($testcomponent, $testinstance, $testlevel, $testuser);
}

/**
 * get authorisation information for this user
 *
 * @deprecated
 * @see SecurityUtil::getAuthInfo()
 * @return array two element array of user and group permissions
 */
function pnSecGetAuthInfo($testuser = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecGetAuthInfo()',
        'SecurityUtil::getAuthInfo()')), 'STRICT');

    return SecurityUtil::getAuthInfo($testuser);
}

/**
 * calculate security level for a test item
 *
 * @deprecated
 * @see SecurityUtil::getSecurityLevel
 * @param perms $ array of permissions to test against
 * @param testrealm $ realm of item under test
 * @param testcomponent $ component of item under test
 * @param testinstance $ instance of item under test
 * @return int matching security level
 */
function pnSecGetLevel($perms, $testrealm, $testcomponent, $testinstance)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecGetLevel()',
        'SecurityUtil::getSecurityLevel()')), 'STRICT');

    return SecurityUtil::getSecurityLevel($perms, $testcomponent, $testinstance);
}

/**
 * generate an authorisation key
 *
 * The authorisation key is used to confirm that actions requested by a
 * particular user have followed the correct path.  Any stage that an
 * action could be made (e.g. a form or a 'delete' button) this function
 * must be called and the resultant string passed to the client as either
 * a GET or POST variable.  When the action then takes place it first calls
 * <code>pnSecConfirmAuthKey()</code> to ensure that the operation has
 * indeed been manually requested by the user and that the key is valid
 *
 * @deprecated
 * @see SecurityUtil::generateAuthKey
 * @param modname $ the module this authorisation key is for (optional)
 * @return string an encrypted key for use in authorisation of operations
 */
function pnSecGenAuthKey($modname = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecGenAuthKey()',
        'SecurityUtil::generateAuthKey()')), 'STRICT');

    return SecurityUtil::generateAuthKey($modname);
}

/**
 * confirm an authorisation key is valid
 *
 * See description of <code>pnSecGenAuthKey</code> for information on
 * this function
 *
 * @deprecated
 * @see SecurityUtil::confirmAuthKey()
 * @return bool true if the key is valid, false if it is not
 */
function pnSecConfirmAuthKey()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecConfirmAuthKey()',
        'SecurityUtil::confirmAuthKey()')), 'STRICT');

    return SecurityUtil::confirmAuthKey();
}

/**
 * Wrapper for new pnSecAuthAction() function
 *
 * @deprecated
 * @see SecurityUtil::checkPermission()
 */
function authorised($testrealm, $testcomponent, $testinstance, $testlevel)
{
    return pnSecAuthAction($testrealm, $testcomponent, $testinstance, $testlevel);
}

/**
 * add security schema
 *
 * @deprecated
 * @param unknown_type $component
 * @param unknown_type $schema
 * @return bool
 */
function pnSecAddSchema($component, $schema)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecAddSchema()',
        'SecurityUtil::registerPermissionSchema()')), 'STRICT');

    return SecurityUtil::registerPermissionSchema($component, $schema);
}

/**
 * addinstanceschemainfo - register an instance schema with the security
 * Will fail if an attempt is made to overwrite an existing schema
 *
 * @deprecated
 * @param unknown_type $component
 * @param unknown_type $schema
 */
function addinstanceschemainfo($component, $schema)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecAddSchema()',
        'SecurityUtil::registerPermissionSchema()')), 'STRICT');
    pnSecAddSchema($component, $schema);
}

/**
 * Translation functions - avoids globals in external code
 */
// Translate level -> name
function accesslevelname($level)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'accesslevelname()',
        'SecurityUtil::accesslevelname()')), 'STRICT');
    return SecurityUtil::accesslevelname($level);
}

/**
 * get access level names
 *
 * @deprecated
 * @return array of access names
 */
function accesslevelnames()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'accesslevelnames()',
        'SecurityUtil::accesslevelnames()')), 'STRICT');
    return SecurityUtil::accesslevelnames();
}

/**
 * get a Time String in the right format
 *
 *
 * @param time $ - prefix string
 * @return mixed string if successfull, false if not
 */
function GetUserTime($time)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated.', 'GetUserTime'), 'STRICT');
    if (empty($time)) {
        return;
    }

    if (pnUserLoggedIn()) {
        $time += (pnUserGetVar('tzoffset') - pnConfigGetVar('timezone_server')) * 3600;
    } else {
        $time += (pnConfigGetVar('timezone_offset') - pnConfigGetVar('timezone_server')) * 3600;
    }

    return ($time);
}

/**
 * get status message from previous operation
 *
 * Obtains any status message, and also destroys
 * it from the session to prevent duplication
 *
 *
 * @deprecated
 * @see LogUtil::getStatusMessages()
 * @return string the status message
 */
function pnGetStatusMsg()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnGetStatusMsg()',
        'LogUtil::getStatusMessages()')), 'STRICT');
    $msgStatus = SessionUtil::getVar('_ZStatusMsg');
    SessionUtil::delVar('_ZStatusMsg');
    $msgError = SessionUtil::getVar('_ZErrorMsg');
    SessionUtil::delVar('_ZErrorMsg');
    // Error message overrides status message
    if (!empty($msgError)) {
        $msgStatus = $msgError;
    }

    return $msgStatus;
}

/**
 * ready operating system output
 *
 * Gets a variable, cleaning it up such that any attempts
 * to access files outside of the scope of the Zikula
 * system is not allowed.
 *
 * @deprecated
 * @see DataUtil::formatForOS()
 * @param var $ variable to prepare
 * @param  $ ...
 * @return mixed prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 **/
function pnVarPrepForOS()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarPrepForOS()',
        'DataUtil::formatForOS()')), 'STRICT');

    $resarray = array();

    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::formatForOS($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * ready user output
 *
 * Gets a variable, cleaning it up such that the text is
 * shown exactly as expected
 *
 * @deprecated
 * @see DataUtil::formatForDisplay
 * @param var $ variable to prepare
 * @param  $ ...
 * @return mixed prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepForDisplay()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarPrepForDisplay()',
        'DataUtil::formatForDisplay()')), 'STRICT');

    $resarray = array();
    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::formatForDisplay($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    } else {
        return $resarray;
    }
}

/**
 * ready HTML output
 *
 * Gets a variable, cleaning it up such that the text is
 * shown exactly as expected, except for allowed HTML tags which
 * are allowed through
 *
 * @deprecated
 * @see DataUtil::formatForDisplayHTML
 * @param var variable to prepare
 * @param ...
 * @return string/array prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepHTMLDisplay()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarPrepHTMLDisplay()',
        'DataUtil::formatForDisplayHTML()')), 'STRICT');

    $resarray = array();
    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::formatForDisplayHTML($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * ready database output
 *
 * Gets a variable, cleaning it up such that the text is
 * stored in a database exactly as expected
 *
 * @deprecated
 * @see DataUtil::formatForStore()
 * @param var $ variable to prepare
 * @param  $ ...
 * @return mixed prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepForStore()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarPrepForStore()',
        'DataUtil::formatForStore()')), 'STRICT');

    $resarray = array();
    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::formatForStore($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * Exit the program after displaying the appropriate messages
 *
 * @deprecated
 * @param msg         The messgage to show
 * @param html        whether or not to generate HTML (can be turned off for command line execution)
 */
if (!function_exists('pn_exit')) {
    function pn_exit($msg, $html = true)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
            'pn_exit()',
            'z_exit()')), 'STRICT');
        z_exit($msg, $html);
    }
}

/**
 * log a string to the designated output destination
 *
 * @deprecated
 * @param file             The file (passed from assertion handler)
 * @param line             The line (passed from assertion handler)
 * @param assert_trigger   The assert trigger (passed from assertion handler)
 */
if (!function_exists('pn_assert_callback_function')) {
    function pn_assert_callback_function($file, $line, $assert_trigger)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated.', 'pn_assert_callback_function()', 'STRICT'));
        return pn_exit(__('Assertion failed'));
    }
}

/* Legacy APIs to be removed at a later date */

/**
 * Get a session variable
 *
 * @deprecated
 * @see SessionUtil::getVar
 * @param sring $name of the session variable to get
 * @param string $default the default value to return if the requested session variable is not set
 * @return string session variable requested
 */
function pnSessionGetVar($name, $default = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSessionGetVar()',
        'SessionUtil::getVar()')), 'STRICT');
    return SessionUtil::getVar($name, $default);
}

/**
 * Set a session variable
 *
 * @deprecated
 * @see SessionUtil::setVar
 * @param string $name of the session variable to set
 * @param value $value to set the named session variable
 * @return bool true
 */
function pnSessionSetVar($name, $value)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSessionsetVar()',
        'SessionUtil::setVar()')), 'STRICT');
    return SessionUtil::setVar($name, $value);
}

/**
 * Delete a session variable
 *
 * @deprecated
 * @see SessionUtil::delVar
 * @param string $name of the session variable to delete
 * @return bool true
 */
function pnSessionDelVar($name)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSessionDelVar()',
        'SessionUtil::delVar()')), 'STRICT');
    return SessionUtil::delVar($name);
}

/**
 * remove censored words
 * @deprecated
 */
function pnVarCensor()
{
    LogUtil::log(__f('Error! The \'pnVarCensor\' function used in \'%s\' is deprecated. Instead, please activate the \'MultiHook\' for this module.', DataUtil::formatForDisplay(pnModGetName())));

    $resarray = array();
    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::censor($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * Clear theme engine compiled templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see $Theme->clear_compiled()
 */
function theme_userapi_clear_compiled()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_clear_compiled', '$Theme->clear_compiled()')), 'STRICT');
    $Theme = Theme::getInstance('Theme');
    $res   = $Theme->clear_compiled();
    return $res;
}

/**
 * Clear theme engine cached templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see $Theme->clear_all_cache()
 */
function theme_userapi_clear_cache()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_clear_cache', '$Theme->clear_all_cache()')), 'STRICT');
    $Theme = Theme::getInstance('Theme');
    $res   = $Theme->clear_all_cache();
    return $res;
}

/**
 * Clear render compiled templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see $Renderer->clear_compiled()
 */
function theme_userapi_render_clear_compiled()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_render_clear_compiled', '$Renderer->clear_compiled()')), 'STRICT');
    $Renderer = Renderer::getInstance();
    $res      = $Renderer->clear_compiled();
    return $res;
}

/**
 * Clear render cached templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see Renderer::clear_cache()
 * @param module the module where to clear the cache, emptys = clear all caches
 * @return true or false
 */
function theme_userapi_render_clear_cache($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_render_clear_cache', '$Renderer->clear_cache()')), 'STRICT');
    if(isset($args['module']) && !empty($args['module']) && pnModAvailable($args['module'])) {
        $Renderer = Renderer::getInstance($args['module']);
        $res      = $Renderer->clear_cache();
    } else {
        $Renderer = Renderer::getInstance();
        $res      = $Renderer->clear_all_cache();
    }

    return $res;
}
