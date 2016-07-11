<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Inject the jQuery Datepicker plugin to the template.
 * @see http://jqueryui.com/demos/datepicker/
 *
 * Available attributes:
 *  - see inline docblocks of each parameter
 *  - additionally, one can set any parameter available in the datepicker documentation
 *    however, parameter names and values will not be validated, simply rendered as is.
 *    case in parameter names must be observed! all jQuery parameter values must be strings.
 *    see datepicker docs for options
 *  - regionalization attributes (i18n) are set in most cases automatically
 *
 * Examples:
 *
 *  Displays the datepicker with the current date as default:
 *
 *  <samp>{jquery_datepicker displayelement='date'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string the html and javascript required to render the datepicker
 */
function smarty_function_jquery_datepicker($params, Zikula_View $view)
{
    /**
     * defaultdate
     * php DateTime object
     * The initial date selected and displayed (default: NULL)
     */
    $defaultDate = (isset($params['defaultdate']) && ($params['defaultdate'] instanceof DateTime)) ? $params['defaultdate'] : null;
    unset($params['defaultdate']);
    /**
     * displayelement
     * string (do not include the '#' character)
     * (required) The id text of the html element where the datepicker displays the selection
     */
    $displayElement = (isset($params['displayelement'])) ? $params['displayelement'] : null;
    unset($params['displayelement']);
    /**
     * displayformat_datetime
     * string
     * (optional) The php Date format used for the display element (default: 'j F Y')
     */
    $displayFormat_dateTime = (isset($params['displayformat_datetime'])) ? $params['displayformat_datetime'] : 'j F Y';
    unset($params['displayformat_datetime']);
    /**
     * displayformat_javascript
     * string
     * (optional) The javascript date format used for display (should mirror the dateTime version) (default: 'd MM yy')
     * @see http://docs.jquery.com/UI/Datepicker/formatDate
     */
    $displayFormat_javascript = (isset($params['displayformat_javascript'])) ? $params['displayformat_javascript'] : 'd MM yy';
    unset($params['displayformat_javascript']);
    /**
     * displayelement_class
     * string
     * (optional) The css class applied to the display element (default: null)
     */
    $displayElement_class = (isset($params['displayelement_class'])) ? $params['displayelement_class'] : null;
    unset($params['displayelement_class']);
    /**
     * valuestorageelement
     * string (do not include the '#' character)
     * (optional) the id text of the html element where the selected date will be stored (default null)
     */
    $valueStorageElement = (isset($params['valuestorageelement'])) ? $params['valuestorageelement'] : null;
    unset($params['valuestorageelement']);
    /**
     * valuestorageformat
     * string
     * (optional) the php Date format used for the date passed to the Form (default: 'Y-m-d')
     */
    $valueStorageFormat_dateTime = (isset($params['valuestorageformat'])) ? $params['valuestorageformat'] : 'Y-m-d';
    unset($params['valuestorageformat']);
    /**
     * valuestorageformat_javascript
     * string
     * (optional) the javascript date format used for the storage element (should mirror the dateTime version) (default: computed from valuestorageformat)
     * @see http://docs.jquery.com/UI/Datepicker/formatDate
     */
    $valueStorageFormat_javascript = (isset($params['valuestorageformat_javascript'])) ? $params['valuestorageformat_javascript'] : str_replace(['Y', 'm', 'd'], ['yy', 'mm', 'dd'], $valueStorageFormat_dateTime);
    unset($params['valuestorageformat_javascript']);
    /**
     * onselectcallback
     * string
     * (optional) javascript to perform onSelect event (default: null)
     */
    $onSelectCallback = (isset($params['onselectcallback'])) ? $params['onselectcallback'] : null;
    unset($params['onselectcallback']);
    /**
     * readonly
     * boolean
     * (optional) whether the display field is readonly or active (default: (boolean)true - IS readonly)
     */
    $readOnly = (isset($params['readonly'])) ? $params['readonly'] : true;
    unset($params['readonly']);
    /**
     * object
     * string
     * (optional) object name for html element names. e.g. name='myObjectName[myVariable]' (default: null)
     */
    $object = (isset($params['object'])) ? $params['object'] : null;
    unset($params['object']);
    /**
     * mindate
     * mixed (php DateTime object or string formatted in same manner as display object
     * (optional) minimum date allowed to be selected in datepicker (default: null - choose any date)
     */
    $minDate = (isset($params['mindate']) && ($params['mindate'] instanceof DateTime)) ? $params['mindate'] : null;
    $minDateString = (isset($params['mindate']) && !($params['mindate'] instanceof DateTime)) ? $params['mindate'] : null;
    unset($params['mindate']);
    /**$minDate = (isset($params['mindate'])) ? $params['mindate'] : null;
    unset($params['mindate']);*/
    /**
     * maxdate
     * mixed (php DateTime object or string formatted in same manner as display object
     * (optional) maximum date allowed to be selected in datepicker (default: null - choose any date)
     */
    $maxDate = (isset($params['maxdate']) && ($params['maxdate'] instanceof DateTime)) ? $params['maxdate'] : null;
    $maxDateString = (isset($params['maxdate']) && !($params['maxdate'] instanceof DateTime)) ? $params['maxdate'] : null;
    unset($params['maxdate']);
    /**
     * theme
     * string
     * (optional) which jquery theme to use for this plugin. Uses JQueryUtil::loadTheme() (default: 'smoothness')
     */
    $jQueryTheme = (isset($params['theme'])) ? $params['theme'] : 'smoothness';
    unset($params['theme']);
    /**
     * lang
     * string
     * (optional) language of datepicker (default: current system language)
     */
    $lang = (isset($params['lang'])) ? $params['lang'] : ZLanguage::getLanguageCode();
    unset($params['lang']);

    // check required params
    if (!isset($displayElement)) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['jquery_datepicker', 'displayelement']));

        return false;
    }

    // load required javascripts
    PageUtil::addVar("javascript", "jquery-ui");
    if (!empty($lang) && ($lang != 'en')) {
        PageUtil::addVar("javascript", "web/jquery-ui/ui/i18n/datepicker-$lang.js");
    }
    $jQueryTheme = is_dir("web/jquery-ui/themes/$jQueryTheme") ? $jQueryTheme : 'smoothness';
    PageUtil::addVar("stylesheet", "web/jquery-ui/themes/$jQueryTheme/jquery-ui.css");

    // build the datepicker
    $javascript = ($defaultDate) ? "
        var {$displayElement}DefaultDate = new Date(\"{$defaultDate->format($displayFormat_dateTime)}\");" : "var {$displayElement}DefaultDate = null";
    if (isset($minDate)) {
        $javascript .= "
        var {$displayElement}minDate = new Date(\"{$minDate->format($displayFormat_dateTime)}\");";
    }
    if (isset($maxDate)) {
        $javascript .= "
        var {$displayElement}maxDate = new Date(\"{$maxDate->format($displayFormat_dateTime)}\");";
    }
    $javascript .= "    
        jQuery(document).ready(function() {
            jQuery('#$displayElement').datepicker({";
    // add additional parameters set in template first
    foreach ($params as $param => $value) {
        $javascript .= "
                $param: $value,";
    }
    // add configured/computed parameters from plugin
    if (isset($valueStorageElement)) {
        $javascript .= "
                altField: '#$valueStorageElement',
                altFormat: '$valueStorageFormat_javascript',";
    }
    if (!empty($minDate)) {
        $javascript .= "
                minDate: {$displayElement}minDate,";
    } elseif (!empty($minDateString)) {
        $javascript .= "
                minDate: '$minDateString',";
    }
    if (!empty($maxDate)) {
        $javascript .= "
                maxDate: {$displayElement}maxDate,";
    } elseif (!empty($maxDateString)) {
        $javascript .= "
                maxDate: '$maxDateString',";
    }
    if (isset($onSelectCallback)) {
        $javascript .= "
                onSelect: function(dateText, inst) {" . $onSelectCallback . "},";
    }
    $javascript .= ($defaultDate) ? "
                dateFormat: '$displayFormat_javascript',
                defaultDate: {$displayElement}DefaultDate
            });
        });" : "
                dateFormat: '$displayFormat_javascript',
                defaultDate: null
            });
        });";
    PageUtil::addVar("footer", "<script type='text/javascript'>$javascript</script>");

    $readOnlyHtml = ($readOnly) ? " readonly='readonly'" : "";

    $name = isset($object) ? "{$object}[{$displayElement}]" : $displayElement;

    // translate month name since DateTime::format() only returns English
    $english = explode(" ", 'January February March April May June July August September October November December');
    $translated = explode(" ", __('January February March April May June July August September October November December'));
    $displayDateString = ($defaultDate) ? str_replace($english, $translated, $defaultDate->format($displayFormat_dateTime)) : '';

    $class = isset($displayElement_class) ? " class='$displayElement_class'" : '';

    $html = "<input type=\"text\"{$readOnlyHtml} id=\"{$displayElement}\"{$class} name=\"{$name}\" value=\"{$displayDateString}\" />\n";
    if (isset($valueStorageElement)) {
        $name = isset($object) ? "{$object}[{$valueStorageElement}]" : $valueStorageElement;
        $html .= '<input type="hidden" id="'.$valueStorageElement.'" name="'.$name.'" value="'.(($defaultDate) ? $defaultDate->format($valueStorageFormat_dateTime) : '').'" />';
    }

    return $html;
}
