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
 * Inject a jQuery Timepicker plugin to the template.
 * @see https://github.com/trentrichardson/jQuery-Timepicker-Addon
 *
 * NOTE: This plugin is NOT configured to integrate the datepicker and timepicker
 *     together as one. It only displays the timepicker.
 *
 * Available attributes:
 *  - see inline docblocks of each parameter
 *  - additionally, one can set any parameter available in the timepicker documentation
 *    however, parameter names and values will not be validated, simply rendered as is.
 *    case in parameter names must be observed! all jQuery parameter values must be strings.
 *    see timepicker docs for options
 *  - regionalization attributes (i18n) are set in most cases automatically
 *
 * Examples:
 *
 *  Displays the timepicker with the current time as default:
 *
 *  <samp>{jquery_timepicker displayelement='time'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object
 *
 * @return string the html and javascript required to render the timepicker
 */
function smarty_function_jquery_timepicker($params, Zikula_View $view)
{
    /**
     * defaultdate
     * php DateTime object
     * The initial datetime selected and displayed (default: now)
     */
    $defaultDate = (isset($params['defaultdate']) && ($params['defaultdate'] instanceof DateTime)) ? $params['defaultdate'] : new DateTime();
    unset($params['defaultdate']);
    /**
     * displayelement
     * string (do not include the '#' character)
     * (required) The id text of the html element where the timepicker displays the selection
     */
    $displayElement = (isset($params['displayelement'])) ? $params['displayelement'] : '';
    unset($params['displayelement']);
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
     * (optional) the id text of the html element where the selected time will be stored (default null)
     * note: storage format is HH:MM (in 24 hour format)
     */
    $valueStorageElement = (isset($params['valuestorageelement'])) ? $params['valuestorageelement'] : null;
    unset($params['valuestorageelement']);
    /**
     * readonly
     * boolean
     * (optional) whether the display field is readonly of active (default: (boolean)true - IS readonly)
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
     * inlinestyle
     * string
     * contents of html style param - useful for setting display:none on load
     */
    $inlineStyle = (isset($params['inlinestyle'])) ? $params['inlinestyle'] : null;
    unset($params['inlinestyle']);
    /**
     * onclosecallback
     * string
     * (optional) javascript to perform onClose event (default: null)
     */
    $onCloseCallback = (isset($params['onclosecallback'])) ? $params['onclosecallback'] : null;
    unset($params['onclosecallback']);
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
    /**
     * use24hour
     * boolean
     * (optional) use 24 hour time display or 12 hour am/pm (default: false)
     */
    $use24hour = (isset($params['use24hour'])) ? $params['use24hour'] : false;
    unset($params['use24hour']);

    // compute formats
    if ($use24hour) {
        $jqueryTimeFormat = 'HH:mm';
        $dateTimeFormat = 'G:i';
    } else {
        $jqueryTimeFormat = 'h:mm tt';
        $dateTimeFormat = 'g:i a';
    }

    // load required javascripts
    PageUtil::addVar("javascript", "jquery-ui");
    if (!System::isDevelopmentMode()) {
        PageUtil::addVar("javascript", "javascript/jquery-plugins/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon.min.js");
        PageUtil::addVar("stylesheet", "javascript/jquery-plugins/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon.min.css");
    } else {
        PageUtil::addVar("javascript", "javascript/jquery-plugins/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon.js");
        PageUtil::addVar("stylesheet", "javascript/jquery-plugins/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon.css");
    }
    if (!empty($lang) && ('en' != $lang)) {
        PageUtil::addVar("javascript", "javascript/jquery-plugins/jQuery-Timepicker-Addon/i18n/jquery-ui-timepicker-$lang.js");
    }
    $jQueryTheme = is_dir("web/jquery-ui/themes/$jQueryTheme") ? $jQueryTheme : 'smoothness';
    PageUtil::addVar("stylesheet", "web/jquery-ui/themes/$jQueryTheme/jquery-ui.css");

    // build the timepicker
    $javascript = "
        jQuery(document).ready(function() {
            jQuery('#$displayElement').timepicker({";
    // add additional parameters set in template first
    foreach ($params as $param => $value) {
        $javascript .= "
                $param: $value,";
    }
    // add configured/computed parameters from plugin
    if (isset($onCloseCallback)) {
        $javascript .= "
                onClose: function(dateText, inst) {" . $onCloseCallback . "},";
    }
    if (isset($valueStorageElement)) {
        addTimepickerFormatTime();
        $javascript .= "
                onSelect: function(dateText, inst) {
                    jQuery('#$valueStorageElement').attr('value', timepickerFormatTime(jQuery(this).datepicker('getDate')));
                },";
        //        note: as of v1.4.3, the altField param doesn't work as expected because it is getting it's default time from
//        somewhere else so, the time in the picker defaults to 00:00 instead of the actual time. So this doesn't work yet:
//        $javascript .= "
//                altField: '#$valueStorageElement',
//                altTimeFormat: 'HH:mm',";
    }
    $javascript .= "
                timeFormat: '$jqueryTimeFormat',
                parse: 'loose'
            });
        });";
    PageUtil::addVar("footer", "<script type='text/javascript'>$javascript</script>");

    $readOnlyHtml = ($readOnly) ? " readonly='readonly'" : "";
    $inlineStyle = (isset($inlineStyle)) ? " style='$inlineStyle'" : '';

    $name = isset($object) ? "{$object}[{$displayElement}]" : $displayElement;
    $class = isset($displayElement_class) ? " class='$displayElement_class'" : '';

    $html = "<input type='text'{$readOnlyHtml}{$inlineStyle} id='$displayElement'{$class} name='$name' value='{$defaultDate->format($dateTimeFormat)}' />\n";
    if (isset($valueStorageElement)) {
        $name = isset($object) ? "{$object}[{$valueStorageElement}]" : $valueStorageElement;
        $html .= "<input type='hidden' id='$valueStorageElement' name='$name' value='{$defaultDate->format('G:i')}' />\n";
    }

    return $html;
}

/**
 * add required JS function to page
 */
function addTimepickerFormatTime()
{
    $javascript = "
        function timepickerFormatTime(date)
        {
            var m = date.getMinutes();
            var h = date.getHours();
            m = m + ''; // convert to string
            h = h + ''; // convert to string
            m = m.length == 1 ? '0' + m : m;
            h = h.length == 1 ? '0' + h : h;

            return h + ':' + m;
        }";
    PageUtil::addVar("footer", "<script type='text/javascript'>$javascript</script>");
}
