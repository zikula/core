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

// start BC classes licensed as LGPv2.1
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
class pnFormRender extends Form_Render
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
        return $this->Execute($template, $eventHandler);
    }
    public function pnFormRegisterPlugin($pluginName, &$params, $isBlock = false)
    {
        return $this->RegisterPlugin($pluginName, $params, $isBlock = false);
    }
    public function pnFormRegisterBlock($pluginName, &$params, &$content)
    {
        $this->RegisterBlock($pluginName, $params, $content);
    }
    public function pnFormRegisterBlockBegin($pluginName, &$params)
    {
        $this->RegisterBlockBegin($pluginName, $params);
    }
    public function pnFormRegisterBlockEnd($pluginName, &$params, $content)
    {
        return $this->RegisterBlockEnd($pluginName, $params, $content);
    }
    public function pnFormGetPluginId(&$params)
    {
        return $this->GetPluginId($params);
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
        $this->AddValidator($validator);
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
        $this->SetState($region, $varName, $varValue);
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
    function &pnFormGetPluginById($id)
    {
        return $this->GetPluginById($id);
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
        $this->GetValues_rec($plugins, $result);
    }
    public function pnFormSetValues(&$values, $group = null)
    {
        return $this->SetValues($values, $group);
    }
    public function pnFormSetValues2(&$values, $group = null, $plugins)
    {
        return $this->SetValues2($values, $group, $plugins);
    }
    public function pnFormSetValues_rec(&$values, $group, $plugins)
    {
        $this->SetValues_rec($values, $group, $plugins);
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
        $time += (pnUserGetVar('tzoffset') - System::getVar('timezone_server')) * 3600;
    } else {
        $time += (System::getVar('timezone_offset') - System::getVar('timezone_server')) * 3600;
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

function pnModInitCoreVars()
{
    return ModUtil::initCoreVars();
}

/**
 * Checks to see if a module variable is set.
 *
 * @param string $modname The name of the module.
 * @param string $name    The name of the variable.
 *
 * @return boolean True if the variable exists in the database, false if not.
 */
function pnModVarExists($modname, $name)
{
    return ModUtil::hasVar($modname, $name);
}

/**
 * The pnModGetVar function gets a module variable.
 *
 * If the name parameter is included then function returns the
 * module variable value.
 * if the name parameter is ommitted then function returns a multi
 * dimentional array of the keys and values for the module vars.
 *
 * @param string  $modname The name of the module.
 * @param string  $name    The name of the variable.
 * @param boolean $default The value to return if the requested modvar is not set.
 *
 * @return string|array If the name parameter is included then function returns
 *          string - module variable value
 *          if the name parameter is ommitted then function returns
 *          array - multi dimentional array of the keys
 *                  and values for the module vars.
 */
function pnModGetVar($modname, $name = '', $default = false)
{
    return ModUtil::getVar($modname, $name, $default);
}


/**
 * The pnModSetVar Function sets a module variable.
 *
 * @param string $modname The name of the module.
 * @param string $name    The name of the variable.
 * @param string $value   The value of the variable.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModSetVar($modname, $name, $value = '')
{
    return ModUtil::setVar($modname, $name, $value);
}

/**
 * The pnModSetVars function sets multiple module variables.
 *
 * @param string $modname The name of the module.
 * @param array  $vars    An associative array of varnames/varvalues.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModSetVars($modname, $vars)
{
    return ModUtil::setVars($modname, $vars);
}

/**
 * The pnModDelVar function deletes a module variable.
 *
 * Delete a module variables. If the optional name parameter is not supplied all variables
 * for the module 'modname' are deleted.
 *
 * @param string $modname The name of the module.
 * @param string $name    The name of the variable (optional).
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModDelVar($modname, $name = '')
{
    return ModUtil::delVar($modname, $name);
}

/**
 * The pnModGetIDFromName function gets module ID given its name.
 *
 * @param string $module The name of the module.
 *
 * @return integer module ID.
 */
function pnModGetIDFromName($module)
{
    return ModUtil::getIdFromName($module);
}

/**
 * The pnModGetInfo function gets information on module.
 *
 * Return array of module information or false if core ( id = 0 ).
 *
 * @param integer $modid The module ID.
 *
 * @return array|boolean Module information array or false.
 */
function pnModGetInfo($modid = 0)
{
    return ModUtil::getInfo($modid);
}

/**
 * The pnModGetUserMods function gets a list of user modules.
 *
 * @return array An array of module information arrays.
 */
function pnModGetUserMods()
{
    return ModUtil::getUserMods();
}

/**
 * The pnModGetProfilesMods function gets a list of profile modules.
 *
 * @return array An array of module information arrays.
 */
function pnModGetProfileMods()
{
    return ModUtil::getProfileMods();
}

/**
 * The pnModGetMessageMods function gets a list of message modules.
 *
 * @return array An array of module information arrays.
 */
function pnModGetMessageMods()
{
    return ModUtil::getMessageMods();
}

/**
 * The pnModGetAdminMods function gets a list of administration modules.
 *
 * @return array An array of module information arrays.
 */
function pnModGetAdminMods()
{
    return ModUtil::getAdminMods();
}

/**
 * The pnModGetTypeMods function gets a list of modules by module type.
 *
 * @param string $type The module type to get (either 'user' or 'admin') (optional) (default='user').
 *
 * @return array An array of module information arrays.
 */
function pnModGetTypeMods($type = 'user')
{
    return ModUtil::getTypeMods($type);
}

/**
 * The pnModGetAllMods function gets a list of all modules.
 *
 * @return array An array of module information arrays.
 */
function pnModGetAllMods()
{
    return ModUtil::getAllMods();
}

/**
 * Loads datbase definition for a module.
 *
 * @param string  $modname   The name of the module to load database definition for.
 * @param string  $directory Directory that module is in (if known).
 * @param boolean $force     Force table information to be reloaded.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModDBInfoLoad($modname, $directory = '', $force = false)
{
    return ModUtil::dbInfoLoad($modname, $directory, $force);
}

/**
 * Loads a module.
 *
 * @param string  $modname The name of the module.
 * @param string  $type    The type of functions to load.
 * @param boolean $force   Determines to load Module even if module isn't active.
 *
 * @return string|boolean Name of module loaded, or false on failure.
 */
function pnModLoad($modname, $type = 'user', $force = false)
{
    return ModUtil::load($modname, $type, $force);
}

/**
 * Load an API module.
 *
 * @param string  $modname The name of the module.
 * @param string  $type    The type of functions to load.
 * @param boolean $force   Determines to load Module even if module isn't active.
 *
 * @return string|boolean Name of module loaded, or false on failure.
 */
function pnModAPILoad($modname, $type = 'user', $force = false)
{
    return ModUtil::loadApi($modname, $type, $force);
}

/**
 * Load a module.
 *
 * @param string  $modname The name of the module.
 * @param string  $type    The type of functions to load.
 * @param boolean $force   Determines to load Module even if module isn't active.
 * @param boolean $api     Whether or not to load an API (or regular) module.
 *
 * @return string|boolean Name of module loaded, or false on failure.
 */
function pnModLoadGeneric($modname, $type = 'user', $force = false, $api = false)
{
    return ModUtil::loadGeneric($modname, $type, $force, $api);
}

/**
 * Run a module function.
 *
 * @param string $modname The name of the module.
 * @param string $type    The type of function to run.
 * @param string $func    The specific function to run.
 * @param array  $args    The arguments to pass to the function.
 *
 * @return mixed.
 */
function pnModFunc($modname, $type = 'user', $func = 'main', $args = array())
{
    return ModUtil::func($modname, $type, $func, $args);
}

/**
 * Run an module API function.
 *
 * @param string $modname The name of the module.
 * @param string $type    The type of function to run.
 * @param string $func    The specific function to run.
 * @param array  $args    The arguments to pass to the function.
 *
 * @return mixed.
 */
function pnModAPIFunc($modname, $type = 'user', $func = 'main', $args = array())
{
    return ModUtil::apiFunc($modname, $type, $func, $args);
}

/**
 * Run a module function.
 *
 * @param string  $modname The name of the module.
 * @param string  $type    The type of function to run.
 * @param string  $func    The specific function to run.
 * @param array   $args    The arguments to pass to the function.
 * @param boolean $api     Whether or not to execute an API (or regular) function.
 *
 * @return mixed.
 */
function pnModFuncExec($modname, $type = 'user', $func = 'main', $args = array(), $api = false)
{
    return ModUtil::exec($modname, $type, $func, $args);
}

/**
 * Generate a module function URL.
 *
 * If the module is non-API compliant (type 1) then
 * a) $func is ignored.
 * b) $type=admin will generate admin.php?module=... and $type=user will generate index.php?name=...
 *
 * @param string       $modname      The name of the module.
 * @param string       $type         The type of function to run.
 * @param string       $func         The specific function to run.
 * @param array        $args         The array of arguments to put on the URL.
 * @param boolean|null $ssl          Set to constant null,true,false $ssl = true not $ssl = 'true'  null - leave the current status untouched,
 *                                   true - create a ssl url, false - create a non-ssl url.
 * @param string       $fragment     The framgment to target within the URL.
 * @param boolean|null $fqurl        Fully Qualified URL. True to get full URL, eg for Redirect, else gets root-relative path unless SSL.
 * @param boolean      $forcelongurl Force pnModURL to not create a short url even if the system is configured to do so.
 * @param boolean      $forcelang    Forcelang.
 *
 * @return sting Absolute URL for call
 */
function pnModURL($modname, $type = 'user', $func = 'main', $args = array(), $ssl = null, $fragment = null, $fqurl = null, $forcelongurl = false, $forcelang=false)
{
    return ModUtil::url($modname, $type, $func, $args, $ssl, $fragment, $fqurl, $forcelang, $forcelang);
}

/**
 * Check if a module is available.
 *
 * @param string  $modname The name of the module.
 * @param boolean $force   Force.
 *
 * @return boolean True if the module is available, false if not.
 */
function pnModAvailable($modname = null, $force = false)
{
    return ModUtil::available($modname, $force);
}

/**
 * Get name of current top-level module.
 *
 * @return string The name of the current top-level module, false if not in a module.
 */
function pnModGetName()
{
    return ModUtil::getName();
}

/**
 * Register a hook function.
 *
 * @param object $hookobject The hook object.
 * @param string $hookaction The hook action.
 * @param string $hookarea   The area of the hook (either 'GUI' or 'API').
 * @param string $hookmodule Name of the hook module.
 * @param string $hooktype   Name of the hook type.
 * @param string $hookfunc   Name of the hook function.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModRegisterHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc)
{
    return ModUtil::registerHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc);
}


/**
 * Unregister a hook function.
 *
 * @param string $hookobject The hook object.
 * @param string $hookaction The hook action.
 * @param string $hookarea   The area of the hook (either 'GUI' or 'API').
 * @param string $hookmodule Name of the hook module.
 * @param string $hooktype   Name of the hook type.
 * @param string $hookfunc   Name of the hook function.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModUnregisterHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc)
{
    return ModUtil::unregisterHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc);
}

/**
 * Carry out hook operations for module.
 *
 * @param string  $hookobject The object the hook is called for - one of 'item', 'category' or 'module'.
 * @param string  $hookaction The action the hook is called for - one of 'new', 'create', 'modify', 'update', 'delete', 'transform', 'display', 'modifyconfig', 'updateconfig'.
 * @param integer $hookid     The id of the object the hook is called for (module-specific).
 * @param array   $extrainfo  Extra information for the hook, dependent on hookaction.
 * @param boolean $implode    Implode collapses all display hooks into a single string - default to true for compatability with .7x.
 *
 * @return string|array String output from GUI hooks, extrainfo array for API hooks.
 */
function pnModCallHooks($hookobject, $hookaction, $hookid, $extrainfo = array(), $implode = true)
{
    return ModUtil::callHooks($hookobject, $hookaction, $hookid, $extrainfo, $implode);
}

/**
 * Determine if a module is hooked by another module.
 *
 * @param string $tmodule The target module.
 * @param string $smodule The source module - default the current top most module.
 *
 * @return boolean True if the current module is hooked by the target module, false otherwise.
 */
function pnModIsHooked($tmodule, $smodule)
{
    return ModUtil::isHooked($tmodule, $smodule);
}

/**
 * The pnModLangLoad function loads the language files for a module.
 *
 * @param string  $modname Name of the module.
 * @param string  $type    Type of the language file to load e.g. user, admin.
 * @param boolean $api     Load api lang file or gui lang file.
 *
 * @return boolean False as this function is depreciated.
 *
 * @deprecated define based language system support stopped with Zikula 1.3.0
 */
function pnModLangLoad($modname, $type = 'user', $api = false)
{
    LogUtil::registerError(__('pnModLangLoad is deprecated.', 404));
    return false;
}

/**
 * Get the base directory for a module.
 *
 * Example: If the webroot is located at
 * /var/www/html
 * and the module name is Template and is found
 * in the modules directory then this function
 * would return /var/www/html/modules/Template
 *
 * If the Template module was located in the system
 * directory then this function would return
 * /var/www/html/system/Template
 *
 * This allows you to say:
 * include(pnModGetBaseDir() . '/includes/private_functions.php');.
 *
 * @param string $modname Name of module to that you want the base directory of.
 *
 * @return string The path from the root directory to the specified module.
 */
function pnModGetBaseDir($modname = '')
{
    return ModUtil::getBaseDir($modname);
}

/**
 * Gets the modules table.
 *
 * Small wrapper function to avoid duplicate sql.
 *
 * @return array An array modules table.
 */
function pnModGetModsTable()
{
    return ModUtil::getModsTable();
}

class ModuleUtil
{
    /**
     * Generic modules select function. Only modules in the module
     * table are returned which means that new/unscanned modules
     * will not be returned
     *
     * @param where The where clause to use for the select
     * @param sort  The sort to use
     *
     * @return The resulting module object array
     */
    public static function getModules ($where='', $sort='displayname')
    {
        return ModUtil::getModules($where, $sort);
    }


    /**
     * Return an array of modules in the specified state, only modules in
     * the module table are returned which means that new/unscanned modules
     * will not be returned
     *
     * @param state    The module state (optional) (defaults = active state)
     * @param sort  The sort to use
     *
     * @return The resulting module object array
     */
    public static function getModulesByState($state=3, $sort='displayname')
    {
        return ModUtil::getModulesByState($state, $sort);
    }
}

// blocks

/**
 * display all blocks in a block position
 * @param $side block position to render
 */
function pnBlockDisplayPosition($side, $echo = true, $implode = true)
{
    return BlockUtil::displayPosition($side, $echo, $implode);
}

/**
 * show a block
 *
 * @param string $modname module name
 * @param string $block name of the block
 * @param array $blockinfo information parameters
 * @return mixed blockinfo array or null
 */
function pnBlockShow($modname, $block, $blockinfo = array())
{
    return BlockUtil::show($modname, $block, $blockinfo);
}

/**
 * Display a block based on the current theme
 */
function pnBlockThemeBlock($row)
{
    return BlockUtil::themeBlock($row);
}

/**
 * load a block
 *
 * @param string $modname module name
 * @param string $block name of the block
 * @return bool true on successful load, false otherwise
 */
function pnBlockLoad($modname, $block)
{
    return BlockUtil::load($modname, $block);
}

/**
 * load all blocks
 * @return array array of blocks
 */
function pnBlockLoadAll()
{
    return BlockUtil::loadAll();
}

/**
 * extract an array of config variables out of the content field of a
 * block
 *
 * @param the $ content from the db
 */
function pnBlockVarsFromContent($content)
{
    return BlockUtil::varsFromContent($content);
}

/**
 * put an array of config variables in the content field of a block
 *
 * @param the $ config vars array, in key->value form
 */
function pnBlockVarsToContent($vars)
{
    return BlockUtil::varsToContent($vars);
}

/**
 * Checks if user controlled block state
 *
 * Checks if the user has a state set for a current block
 * Sets the default state for that block if not present
 *
 * @access private
 */
function pnCheckUserBlock($row)
{
    return BlockUtil::checkUserBlock($row);
}

/**
 * get block information
 * @param value the value to search for
 * @param assocKey the field in which we look for the value (optional) (default='bid')
 * @return array array of block information
 */
function pnBlocksGetInfo()
{
    return BlockUtil::getBlocksInfo();
}

/**
 * get block information
 * @param value the value to search for
 * @param assocKey the field in which we look for the value (optional) (default='bid')
 * @return array array of block information
 */
function pnBlockGetInfo($value, $assocKey = 'bid')
{
    return BlockUtil::getBlockInfo($value, $assocKey);
}

/**
 * get block information
 * @param title the block title
 * @return array array of block information
 */
function pnBlockGetInfoByTitle($title)
{
    return BlockUtil::getInfoByTitle($title);
}

/**
 * alias to pnBlockDisplayPosition
 */
function blocks($side)
{
    return BlockUtil::displayPosition($side);
}

/**
 * alias to pnBlockDisplayPosition
 */
function themesideblock($row)
{
    return BlockUtil::themesideblock($row);
}

// user

/**
 * Log the user in
 *
 * @param uname $ the name of the user logging in
 * @param pass $ the password of the user logging in
 * @param rememberme whether $ or not to remember this login
 * @param checkPassword bool true whether or not to check the password
 * @return bool true if the user successfully logged in, false otherwise
 */
function pnUserLogIn($uname, $pass, $rememberme = false, $checkPassword = true)
{
    return UserUtil::login($uname, $pass, $rememberme, $checkPassword);
}

/**
 * Log the user in via the REMOTE_USER SERVER property. This routine simply
 * checks if the REMOTE_USER exists in the PN environment: if he does a
 * session is created for him, regardless of the password being used.
 *
 * @return bool true if the user successfully logged in, false otherwise
 */
function pnUserLogInHTTP()
{
    return UserUtil::loginHttp();
}

/**
 * Log the user out
 *
 * @public
 * @return bool true if the user successfully logged out, false otherwise
 */
function pnUserLogOut()
{
    return UserUtil::logout();
}

/**
 * is the user logged in?
 *
 * @public
 * @returns bool true if the user is logged in, false if they are not
 */
function pnUserLoggedIn()
{
    return UserUtil::isLoggedIn();
}

/**
 * Get all user variables, maps new style attributes to old style user data.
 *
 * @param uid $ the user id of the user
 * @return array an associative array with all variables for a user
 */
function pnUserGetVars($id, $force = false, $idfield = '')
{
    return UserUtil::getVars($id, $force, $idfield);
}

/**
 * get a user variable
 *
 * @param name $ the name of the variable
 * @param uid $ the user to get the variable for
 * @param default $ the default value to return if the specified variable doesn't exist
 * @return string the value of the user variable if successful, null otherwise
 */
function pnUserGetVar($name, $uid = -1, $default = false)
{
    return UserUtil::getVar($name, $uid, $default);
}

/**
 * Set a user variable. This can be
 * - a field in the users table
 * - or an attribute and in this case either a new style attribute or an old style user information.
 *
 * Examples:
 * pnUserSetVar('pass', 'mysecretpassword'); // store a password (should be hashed of course)
 * pnUserSetVar('avatar', 'mypicture.gif');  // stores an users avatar, new style
 * (internally both the new and the old style write the same attribute)
 *
 * If the user variable does not exist it will be created automatically. This means with
 * pnUserSetVar('somename', 'somevalue');
 * you can easily create brand new users variables onthefly.
 *
 * This function does not allow you to set uid or uname.
 *
 * @param name $ the name of the variable
 * @param value $ the value of the variable
 * @param uid $ the user to set the variable for
 * @return bool true if the set was successful, false otherwise
 */
function pnUserSetVar($name, $value, $uid = -1)
{
    return UserUtil::setVar($name, $value, $uid);
}

function pnUserSetPassword($pass)
{
    return UserUtil::setVar($name, $value, $uid);
}

/**
 * Delete the contents of a user variable. This can either be
 * - a variable stored in the users table or
 * - an attribute to the users table, either a new style sttribute or the old style user information
 *
 * Examples:
 * pnUserDelVar('ublock');  // clears the recent users table entry for 'ublock'
 * pnUserDelVar('_YOURAVATAR', 123), // removes a users avatar, old style (uid = 123)
 * pnUserDelVar('avatar', 123);  // removes a users avatar, new style (uid=123)
 * (internally both the new style and the old style clear the same attribute)
 *
 * It does not allow the deletion of uid, email, uname and pass (word) as these are mandatory
 * fields in the users table.
 *
 * @param name $ the name of the variable
 * @param uid $ the user to delete the variable for
 * @return boolen true on success, false on failure
 */
function pnUserDelVar($name, $uid = -1)
{
    return UserUtil::delVar($name, $uid);
}

/**
 * get the user's theme
 * This function will return the current theme for the user.
 * Order of theme priority:
 *  - page-specific
 *  - category
 *  - user
 *  - system
 *
 * @public
 * @return string the name of the user's theme
 **/
function pnUserGetTheme($force = false)
{
    return UserUtil::getTheme($force);
}

/**
 * get the user's language
 *
 * @deprecated
 * @see ZLanaguage::getLanguageCode()
 *
 * This function returns the deprecated 3 digit language codes, you need to switch APIs
 *
 * @return string the name of the user's language
 */
function pnUserGetLang()
{
    return UserUtil::getLang();
}

/**
 * get a list of user information
 *
 * @public
 * @return array array of user arrays
 */
function pnUserGetAll($sortbyfield = 'uname', $sortorder = 'ASC', $limit = -1, $startnum = -1, $activated = '', $regexpfield = '', $regexpression = '', $where = '')
{
    return UserUtil::getAll($sortbyfield, $sortorder, $limit, $startnum, $activated, $regexpfield, $regexpression, $where);
}

/**
 * Get the uid of a user from the username
 *
 * @param uname $ the username
 * @return mixed userid if found, false if not
 */
function pnUserGetIDFromName($uname)
{
    return UserUtil::getIdFromName($uname);
}

/**
 * Get the uid of a user from the email (case for unique emails)
 *
 * @param email $ the user email
 * @return mixed userid if found, false if not
 */
function pnUserGetIDFromEmail($email)
{
    return UserUtil::getIdFromEmail($email);
}

/**
 * Checks the alias and returns if we save the data in the
 * Profile module's user_data table or the users table.
 * This should be removed if we ever go fully dynamic
 *
 * @param label $ the alias of the field to check
 * @return true if found, false if not, void upon error
 */
function pnUserFieldAlias($label)
{
    return UserUtil::fieldAlias($label);
}

/**
 * Loader class
 *
 */
class Loader
{
    /**
     * Load a file from the specified location in the file tree
     *
     * @param fileName    The name of the file to load
     * @param path        The path prefix to use (optional) (default=null)
     * @param exitOnError whether or not exit upon error (optional) (default=true)
     * @param returnVar   The variable to return from the sourced file (optional) (default=null)
     *
     * @return string The file which was loaded
     */
    public static function loadFile($fileName, $path = null, $exitOnError = true, $returnVar = null)
    {
        if (!$fileName) {
            return pn_exit(__f("Error! Invalid file specification '%s'.", $fileName));
        }

        $file = null;
        if ($path) {
            $file = "$path/$fileName";
        } else {
            $file = $fileName;
        }

        $file = DataUtil::formatForOS($file);

        if (is_file($file) && is_readable($file)) {
            if (include_once ($file)) {
                if ($returnVar) {
                    return $$returnVar;
                } else {
                    return $file;
                }
            }
        }

        if ($exitOnError) {
            return pn_exit(__f("Error! Could not load the file '%s'.", $fileName));
        }

        return false;
    }

    /**
     * Load all files from the specified location in the pn file tree
     *
     * @param files        An array of filenames to load
     * @param path         The path prefix to use (optional) (default='null')
     * @param exitOnError  whether or not exit upon error (optional) (default=true)
     *
     * @return boolean true
     */
    public static function loadAllFiles($files, $path = null, $exitOnError = false)
    {
        return self::loadFiles($files, $path, true, $exitOnError);
    }

    /**
     * Return after the first successful file load. This corresponds to the
     * default behaviour of loadFiles().
     *
     * @param files        An array of filenames to load
     * @param path         The path prefix to use (optional) (default='null')
     * @param exitOnError  whether or not exit upon error (optional) (default=true)
     *
     * @return boolean true
     */
    public static function loadOneFile($files, $path = null, $exitOnError = false)
    {
        return self::loadFiles($files, $path, false, $exitOnError);
    }

    /**
     * Load multiple files from the specified location in the pn file tree
     * Note that in it's default invokation, this method exits after the
     * first successful file load.
     *
     * @param files       Array of filenames to load
     * @param path        The path prefix to use (optional) (default='null')
     * @param all         whether or not to load all files or exit upon 1st successful load (optional) (default=false)
     * @param exitOnError whether or not exit upon error (optional) (default=true)
     * @param returnVar   The variable to return if $all==false (optional) (default=null)
     *
     * @return boolean true
     */
    public static function loadFiles($files, $path = null, $all = false, $exitOnError = false, $returnVar = '')
    {
        if (!is_array($files) || !$files) {
            return pn_exit(__('Error! Invalid file array specification.'));
        }

        $files = array_unique($files);

        $loaded = false;
        foreach ($files as $file) {
            $rc = self::loadFile($file, $path, $exitOnError, $returnVar);

            if ($rc) {
                $loaded = true;
            }

            if ($loaded && !$all) {
                break;
            }
        }

        if ($returnVar && !$all) {
            return $rc;
        }

        return $loaded;
    }

    /**
     * Load a class file from the specified location in the file tree
     *
     * @param className    The class-basename to load
     * @param classPath    The path prefix to use (optional) (default='lib')
     * @param exitOnError  whether or not exit upon error (optional) (default=true)
     *
     * @return string The file name which was loaded
     */
    public static function loadClass($className, $classPath = 'lib', $exitOnError = true)
    {
        if (!$className) {
            return pn_exit(__f("Error! Invalid class specification '%s'.", $className));
        }

        if (class_exists($className)) {
            return $className;
        }

        $classFile = $className . '.class.php';
        $rc = self::loadFile($classFile, "config/classes/$classPath", false);
        if (!$rc) {
            $rc = self::loadFile($classFile, $classPath, $exitOnError);
        }

        return $rc;
    }

    /**
     * Load a PNObject extended class from the given module. The given class name is
     * prefixed with 'PN' and underscores are removed to produce a proper class name.
     *
     * @param module        The module to load from
     * @param base_obj_type The base object type for which to load the class
     * @param array         If true, load the array class instead of the single-object class.
     * @param exitOnError   whether or not exit upon error (optional) (default=true)
     * @param prefix        Override parameter for the default PN prefix (default=PN)
     *
     * @return string The ClassName which was loaded from the file
     */
    public static function loadClassFromModule($module, $base_obj_type, $array = false, $exitOnError = false, $prefix = 'PN')
    {
        if (!$module) {
            return pn_exit(__f("Error! Invalid module specification '%s'.", $module));
        }

        if (!$base_obj_type) {
            return pn_exit(__f("Error! Invalid 'base_obj_type' specification '%s'.", $base_obj_type));
        }

        $prefix = (string) $prefix;

        if (strpos($base_obj_type, '_') !== false) {
            $c = $base_obj_type;
            $class = '';
            while (($p = strpos($c, '_')) !== false) {
                $class .= ucwords(substr($c, 0, $p));
                $c = substr($c, $p + 1);
            }
            $class .= ucwords($c);
        } else {
            $class = ucwords($base_obj_type);
        }

        $class = $prefix . $class;
        if ($array) {
            $class .= 'Array';
        }

        // prevent unncessary reloading
        if (class_exists($class)) {
            return $class;
        }

        $classFiles = array();
        $classFiles[] = "config/classes/$module/{$class}.class.php";
        $classFiles[] = "system/$module/classes/{$class}.class.php";
        $classFiles[] = "modules/$module/classes/{$class}.class.php";

        foreach ($classFiles as $classFile) {
            $classFile = DataUtil::formatForOS($classFile);
            if (is_readable($classFile)) {
                if (self::includeOnce($classFile)) {
                    return $class;
                }

                if ($exitOnError) {
                    return pn_exit(__f('Error! Unable to load class [%s]', $classFile));
                }

                return false;
            }
        }

        return false;
    }

    /**
     * Load a PNObjectArray extended class from the given module. The given class name is
     * prefixed with 'PN' and underscores are removed to produce a proper class name.
     *
     * @param module        The module to load from
     * @param base_obj_type The base object type for which to load the class
     * @param exitOnError   whether or not exit upon error (optional) (default=true)
     * @param prefix        Override parameter for the default PN prefix (default=PN)
     *
     * @return string The ClassName which was loaded from the file
     */
    public static function loadArrayClassFromModule($module, $base_obj_type, $exitOnError = false, $prefix = 'PN')
    {
        return self::loadClassFromModule($module, $base_obj_type, true, $exitOnError, $prefix);
    }

    /**
     * Internal include_once
     *
     * @deprecated
     * @return bool True if file was included - false if not found or included before.
     */
    public static function includeOnce($file)
    {
        return include_once ($file);
    }

    /**
     * Internal require_once
     *
     * @deprecated
     * @param string $file
     * @return bool
     */
    public static function requireOnce($file)
    {
        return require_once ($file);
    }
}