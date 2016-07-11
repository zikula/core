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
 * Display a calendar input control.
 *
 * Display a calendar input control consisting of a calendar image, an optional
 * hidden input field, and associated javascript to render a pop-up calendar.
 * This function displays a javascript (jscalendar) calendar control.
 *
 * Available attributes:
 *   - objectname       (string)    The name of the object the field will be placed in
 *   - htmlname:        (string)    The html fieldname under which the date value will be submitted
 *   - dateformat:      (string)    The dateformat to use for displaying the chosen date
 *   - ifformat:        (string)    Format of the date field sent in the form (optional - defaults to dateformat)
 *   - defaultstring    (string)    The String to display before a value has been selected
 *   - defaultdate:     (string)    The Date the calendar should to default to (format: Y/m/d)
 *   - hidden:          (bool)      If set, a hidden input field will be generated to hold the selected date
 *   - display:         (bool)      If set, a <span> is generated to display the selected date (when date is added in a hidden field)
 *   - class:           (string)    The class to apply to the html elements
 *   - time:            (bool)      If set, show time selection
 *
 * Example:
 *
 * <samp>{calendarinput objectname='myobject' htmlname='from' dateformat='%Y-%m-%d' defaultdate='2005/12/31'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string The HTML and Javascript code to display a calendar control.
 */
function smarty_function_calendarinput($params, Zikula_View $view)
{
    if (!isset($params['objectname'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['calendarinput', 'objectname']));

        return false;
    }
    if (!isset($params['htmlname'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['calendarinput', 'htmlname']));

        return false;
    }
    if (!isset($params['dateformat'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['calendarinput', 'dateformat']));

        return false;
    }
    $ifformat = isset($params['ifformat']) ? $params['ifformat'] : $params['dateformat'];
    $inctime  = isset($params['time']) ? (bool)$params['time'] : false;

    $validformats = ['%Y-%m-%d', '%Y-%m-%d %H:%M'];
    if (!in_array($ifformat, $validformats)) {
        $ifformat = $inctime ? '%Y-%m-%d %H:%M' : '%Y-%m-%d';
    }

    // start of old pncalendarinit
    // pagevars make an extra pncalendarinit obsolete, they take care about the fact
    // that the styles/jsvascript do not get loaded multiple times
    static $firstTime = true;

    if ($firstTime) {
        $lang = ZLanguage::transformFS(ZLanguage::getLanguageCode());
        // map of the jscalendar supported languages
        $map = [
            'ca' => 'ca_ES', 'cz' => 'cs_CZ', 'da' => 'da_DK',
            'de' => 'de_DE', 'el' => 'el_GR', 'en-us' => 'en_US',
            'es' => 'es_ES', 'fi' => 'fi_FI', 'fr' => 'fr_FR',
            'he' => 'he_IL', 'hr' => 'hr_HR', 'hu' => 'hu_HU',
            'it' => 'it_IT', 'ja' => 'ja_JP', 'ko' => 'ko_KR',
            'lt' => 'lt_LT', 'lv' => 'lv_LV', 'nl' => 'nl_NL',
            'no' => 'no_NO', 'pl' => 'pl_PL', 'pt' => 'pt_BR',
            'ro' => 'ro_RO', 'ru' => 'ru_RU', 'si' => 'si_SL',
            'sk' => 'sk_SK', 'sv' => 'sv_SE', 'tr' => 'tr_TR'
        ];

        if (isset($map[$lang])) {
            $lang = $map[$lang];
        }

        $headers[] = 'javascript/jscalendar/calendar.js';
        if (file_exists("javascript/jscalendar/lang/calendar-$lang.utf8.js")) {
            $headers[] = "javascript/jscalendar/lang/calendar-$lang.utf8.js";
        }
        $headers[] = 'javascript/jscalendar/calendar-setup.js';
        PageUtil::addVar('stylesheet', 'javascript/jscalendar/calendar-win2k-cold-2.css');
        PageUtil::addVar('javascript', $headers);
    }
    $firstTime = false;
    // end of old pncalendarinit

    if (!isset($params['defaultstring'])) {
        $params['defaultstring'] = null;
    }
    if (!isset($params['defaultdate'])) {
        $params['defaultdate'] = null;
    }

    $html = '';

    $fieldKey = $params['htmlname'];
    if ($params['objectname']) {
        $fieldKey = $params['objectname'] . '[' . $params['htmlname'] . ']';
    }

    $triggerName = 'trigger_' . $params['htmlname'];
    $displayName = 'display_' . $params['htmlname'];

    if (isset($params['class']) && !empty($params['class'])) {
        $params['class'] = ' class="' . DataUtil::formatForDisplay($params['class']) . '"';
    } else {
        $params['class'] = '';
    }

    if (isset($params['display']) && $params['display']) {
        $html .= '<span id="'.$displayName.'"'.$params['class'].'>'.$params['defaultstring'].'</span>&nbsp;';
    }

    if (isset($params['hidden']) && $params['hidden']) {
        $html .= '<input type="hidden" name="'.$fieldKey.'" id="'.$params['htmlname'].'" value="'.$params['defaultdate'].'" />';
    }

    $html .= '<img class="z-calendarimg" src="'.System::getBaseUrl().'javascript/jscalendar/img.gif" id="'.$triggerName.
    '" style="cursor: pointer;" title="' . DataUtil::formatForDisplay(__('Date selector')) . '"  alt="' . DataUtil::formatForDisplay(__('Date selector')) . '" />';

    $i18n = ZI18n::getInstance();

    $html .= "<script type=\"text/javascript\">
              // <![CDATA[
              Calendar.setup(
              {";

    //$html .= 'ifFormat    : "%Y-%m-%d %H:%M:00",'; // universal format, don't change this!
    $html .= 'ifFormat    : "'.$ifformat.'",';
    $html .= 'inputField  : "'.$params['htmlname'].'",';
    $html .= 'displayArea : "'.$displayName.'",';
    $html .= 'daFormat    : "'.$params['dateformat'].'",';
    $html .= 'button      : "'.$triggerName.'",';
    $html .= 'defaultDate : "'.$params['defaultdate'].'",';
    $html .= 'firstDay    : "'.$i18n->locale->getFirstweekday().'",';
    $html .= 'align       : "Tl",';

    if (isset($params['defaultdate']) && $params['defaultdate']) {
        $d = strtotime($params['defaultdate']);
        $d = date('Y/m/d', $d);
        $html .= 'date : "'.$d.'",';
    }

    if ($inctime) {
        $html .= 'showsTime  : true,';
        $html .= 'timeFormat : "'.$i18n->locale->getTimeformat().'",';
    }

    $html .= "singleClick : true });
              // ]]>
              </script>";

    return $html;
}
