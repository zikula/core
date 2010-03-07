<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * ZLocale class
 */
class ZLocale implements ArrayAccess
{
    // public properties
    private $locale;
    private $errors = array();
    private $localeData = array(
        'language_direction' => 'ltr',
        'decimal_point' => '.',
        'thousands_sep' => ',',
        'int_curr_symbol' => 'EUR',
        'currency_symbol' => '€',
        'mon_decimal_point' => '.',
        'mon_thousands_sep' => ',',
        'positive_sign' => '',
        'negative_sign' => '-',
        'int_frac_digits' => '2',
        'frac_digits' => '2',
        'p_cs_precedes' => '1',
        'p_sep_by_space' => '1',
        'n_cs_precedes' => '1',
        'n_sep_by_space' => '1',
        'p_sign_posn' => '1',
        'n_sign_posn' => '2',
        'firstweekday' => '0',
        'timeformat' => '24',
        'grouping' => array(),
        'mon_grouping' => array());

    public function __construct($locale)
    {
        $this->locale = $locale;
        $this->loadLocaleConfig();
        $this->detectErrors();
    }


    private function loadLocaleConfig()
    {
        $lang = ZLanguage::transformFS($this->locale);
        $override = "config/locale/$lang/locale.ini";
        $file = (file_exists($override) ? $override : "locale/$lang/locale.ini");
        if (is_readable($file)) {
            $array = DataUtil::parseIniFile($file);
            foreach ($array as $k => $v) {
                $k = strtolower($k);
                if ($k == "grouping" || $k == "mon_grouping") {
                    $v = explode(',', $v);
                }
                if (!is_array($v)) {
                    $v = strtolower(trim(trim($v, '"'), "'"));
                }
                $this->localeData[$k] = $v;
            }
            $this->validateLocale($file);
        } else {
            $this->registerError(__f("Error! Could not load '%s'. Please check that it exists.", $file));
        }
    }


    private function validateLocale($file)
    {
        if (count($this->localeData) == 0) {
            $this->registerError(__f('Error! The locale file %s contains invalid data.', array($file)));
            return;
        }
        $validationArray = array('language_direction' => '#^(ltr|rtl)$#');
        foreach ($validationArray as $key => $validation) {
            if (!isset($this->localeData[$key])) {
                $this->registerError(__f('Error! %1$s is missing in %2$s.', array(
                    $key,
                    $file)));
            } else {
                if (!preg_match($validation, $this->localeData[$key])) {
                    $this->registerError(__f('Error! There is an invalid value for %1$s in %2$s.', array(
                        $key,
                        $file)));
                }
            }
        }
    }

    private function registerError($msg)
    {
        $this->errors[] = array($msg);
    }

    private function detectErrors()
    {
        if (count($this->errors) == 0) {
            return true;
        }

        // fatal errors require 404
        header('HTTP/1.1 404 Not Found');
        foreach ($this->errors as $error) {
            LogUtil::registerError($error);
        }

        return false;
    }

    public function offsetExists($offset)
    {
        return (isset($this->$offset));
    }

    public function offsetGet($offset)
    {
        return $this->localeData[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->localeData[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->localeData[$offset]);
    }
}

