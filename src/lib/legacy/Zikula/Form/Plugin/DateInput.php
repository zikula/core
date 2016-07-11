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
 * Date input for Zikula_Form_View
 *
 * The date input plugin is a text input plugin that only allows dates to be posted. The value
 * returned from {@link Zikula_Form_View::GetValues()} is although a string of the format 'YYYY-MM-DD'
 * since this is the standard internal Zikula format for dates.
 *
 * You can also use all of the features from the Zikula_Form_Plugin_TextInput plugin since the date input
 * inherits from it.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_DateInput extends Zikula_Form_Plugin_TextInput
{
    /**
     * Enable or disable input of time in addition to the date.
     *
     * @var boolean
     */
    public $includeTime;

    /**
     * The initial date.
     *
     * @var string
     */
    public $initDate;

    /**
     * Date format in the input field.
     *
     * @var string
     */
    public $ifFormat;

    /**
     * Date format in the display area.
     *
     * @var string
     */
    public $daFormat;

    /**
     * Default date value.
     *
     * This parameter enables the input to be pre-filled with the current date or similar other well defined
     * default values.
     * You can set the default value to be one of the following:
     * - now: current date and time
     * - today: current date
     * - monthstart: first day in current month
     * - monthend: last day in current month
     * - yearstart: first day in the year
     * - yearend: last day in the year
     * - custom: inital Date.
     *
     * @var string
     */
    public $defaultValue;

    /**
     * Enable or disable selection only mode (with hidden input field), defaults to false.
     *
     * @var boolean
     */
    public $useSelectionMode;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
        $this->includeTime = (array_key_exists('includeTime', $params) ? $params['includeTime'] : 0);
        $this->daFormat = (array_key_exists('daFormat', $params) ? $params['daFormat'] : ($this->includeTime ? __('%A, %B %d, %Y - %I:%M %p') : __('%A, %B %d, %Y')));
        $this->ifFormat = (array_key_exists('ifFormat', $params) ? $params['ifFormat'] : ($this->includeTime ? __('%Y-%m-%d %H:%M') : __('%Y-%m-%d')));

        $this->defaultValue = (array_key_exists('defaultValue', $params) ? $params['defaultValue'] : null);
        $this->initDate = (array_key_exists('initDate', $params) ? $params['initDate'] : 0);
        $this->useSelectionMode = (array_key_exists('useSelectionMode', $params) ? $params['useSelectionMode'] : 0);
        $this->maxLength = ($this->includeTime ? 19 : 12);
        $params['width'] = ($this->includeTime ? '10em' : '8em');

        parent::create($view, $params);

        $this->cssClass .= ' z-form-date';
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        static $firstTime = true;

        $i18n = ZI18n::getInstance();

        if (!empty($this->defaultValue) && !$view->isPostBack() && empty($this->text)) {
            $d = strtolower($this->defaultValue);
            $now = getdate();
            $date = null;

            if ($d == 'now') {
                $date = time();
            } elseif ($d == 'today') {
                $date = mktime(0, 0, 0, $now['mon'], $now['mday'], $now['year']);
            } elseif ($d == 'monthstart') {
                $date = mktime(0, 0, 0, $now['mon'], 1, $now['year']);
            } elseif ($d == 'monthend') {
                $daysInMonth = date('t');
                $date = mktime(0, 0, 0, $now['mon'], $daysInMonth, $now['year']);
            } elseif ($d == 'yearstart') {
                $date = mktime(0, 0, 0, 1, 1, $now['year']);
            } elseif ($d == 'yearend') {
                $date = mktime(0, 0, 0, 12, 31, $now['year']);
            } elseif ($d == 'custom') {
                $date = strtotime($this->initDate);
            }

            if ($date != null) {
                $this->text = DateUtil::getDatetime($date, $this->ifFormat, false);
            } else {
                $this->text = __('Unknown date');
            }
        }

        if ($view->isPostBack() && !empty($this->text)) {
            $date = strtotime($this->text);
            $this->text = DateUtil::getDatetime($date, $this->ifFormat, false);
        }

        if ($firstTime) {
            $lang = ZLanguage::transformFS(ZLanguage::getLanguageCode());
            // map of the jscalendar supported languages
            $map = [
                'ca' => 'ca_ES',
                'cz' => 'cs_CZ',
                'da' => 'da_DK',
                'de' => 'de_DE',
                'el' => 'el_GR',
                'en-us' => 'en_US',
                'es' => 'es_ES',
                'fi' => 'fi_FI',
                'fr' => 'fr_FR',
                'he' => 'he_IL',
                'hr' => 'hr_HR',
                'hu' => 'hu_HU',
                'it' => 'it_IT',
                'ja' => 'ja_JP',
                'ko' => 'ko_KR',
                'lt' => 'lt_LT',
                'lv' => 'lv_LV',
                'nl' => 'nl_NL',
                'no' => 'no_NO',
                'pl' => 'pl_PL',
                'pt' => 'pt_BR',
                'ro' => 'ro_RO',
                'ru' => 'ru_RU',
                'si' => 'si_SL',
                'sk' => 'sk_SK',
                'sv' => 'sv_SE',
                'tr' => 'tr_TR'
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

        $result = '';

        if ($this->useSelectionMode) {
            $hiddenInputField = str_replace(['type="text"', '&nbsp;*'],
                                            ['type="hidden"', ''],
                                            parent::render($view));

            $result .= '<div>' . $hiddenInputField . '<span id="' . $this->id . 'cal" style="background-color: #ff8; cursor: default" onmouseover="this.style.backgroundColor=\'#ff0\';" onmouseout="this.style.backgroundColor=\'#ff8\';">';
            if ($this->text) {
                $result .= DataUtil::formatForDisplay(DateUtil::getDatetime(DateUtil::parseUIDate($this->text, $this->ifFormat), $this->daFormat));
            } else {
                $result .= __('Select date');
            }
            $result .= '</span></div>';
            if ($this->mandatory && $this->mandatorysym) {
                $result .= '<span class="z-form-mandatory-flag">*</span>';
            }
        } else {
            $result .= '<span class="z-form-date" style="white-space: nowrap">';
            $result .= parent::render($view);

            $txt = __('Select date');
            $result .= " <img id=\"{$this->id}_img\" src=\"javascript/jscalendar/img.gif\" style=\"vertical-align: middle\" class=\"clickable\" alt=\"{$txt}\" /></span>";
        }

        // build jsCalendar script options
        $result .= "<script type=\"text/javascript\">
            // <![CDATA[
            Calendar.setup(
            {
                inputField : \"{$this->id}\",";

        if ($this->includeTime) {
            $this->initDate = str_replace('-', ',', $this->initDate);
            $result .= "
                    ifFormat : \"" . $this->ifFormat . "\",
                    showsTime      :    true,
                    timeFormat     :    \"" . $i18n->locale->getTimeformat() . "\",
                    singleClick    :    false,";
        } else {
            $result .= "
                    ifFormat : \"" . $this->ifFormat . "\",";
        }

        if ($this->useSelectionMode) {
            $result .= "
                    displayArea :    \"{$this->id}cal\",
                    daFormat    :    \"{$this->daFormat}\",
                    align       :    \"Bl\",
                    singleClick :    true,";
        } else {
            $result .= "
                    button : \"{$this->id}_img\",";
        }

        $result .= "
                    firstDay: " . $i18n->locale->getFirstweekday() . "
                }
            );
            // ]]>
            </script>";

        return $result;
    }

    /**
     * Parses a value.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param string           $text Text.
     *
     * @return string Parsed Text.
     */
    public function parseValue(Zikula_Form_View $view, $text)
    {
        if (empty($text)) {
            return null;
        }

        return $text;
    }

    /**
     * Validates the input string.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function validate(Zikula_Form_View $view)
    {
        parent::validate($view);

        if (!$this->isValid) {
            return;
        }

        if (strlen($this->text) > 0) {
            if ($this->includeTime) {
                $dateValue = DateUtil::transformInternalDateTime(DateUtil::parseUIDate($this->text, $this->ifFormat));
            } else {
                $dateValue = DateUtil::transformInternalDate(DateUtil::parseUIDate($this->text, $this->ifFormat));
            }

            if ($dateValue == null) {
                $this->setError(__('Error! Invalid date.'));
            } else {
                // the date validated so we can use the transformed date
                $this->text = $dateValue;
            }
        }
    }

    /**
     * Format the value to specific format.
     *
     * @param Zikula_Form_View $view  Reference to Zikula_Form_View object.
     * @param string           $value The value to format.
     *
     * @return string Formatted value.
     */
    public function formatValue(Zikula_Form_View $view, $value)
    {
        return DateUtil::formatDatetime($value, $this->ifFormat, false);
    }
}
