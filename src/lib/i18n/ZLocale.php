<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package I18n
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

    // getters

    /**
     * @return the $n_sign_posn
     */
    public function getN_sign_posn()
    {
        return $this->localeData['n_sign_posn'];
    }

    /**
     * @return the $p_sign_posn
     */
    public function getP_sign_posn()
    {
        return $this->localeData['p_sign_posn'];
    }

    /**
     * @return the $n_sep_by_space
     */
    public function getN_sep_by_space()
    {
        return $this->localeData['n_sep_by_space'];
    }

    /**
     * @return the $n_cs_precedes
     */
    public function getN_cs_precedes()
    {
        return $this->localeData['n_cs_precedes'];
    }

    /**
     * @return the $p_sep_by_space
     */
    public function getP_sep_by_space()
    {
        return $this->localeData['p_sep_by_space'];
    }

    /**
     * @return the $p_cs_precedes
     */
    public function getP_cs_precedes()
    {
        return $this->localeData['p_cs_precedes'];
    }

    /**
     * @return the $frac_digits
     */
    public function getFrac_digits()
    {
        return $this->localeData['frac_digits'];
    }

    /**
     * @return the $int_frac_digits
     */
    public function getInt_frac_digits()
    {
        return $this->localeData['int_frac_digits'];
    }

    /**
     * @return the $negative_sign
     */
    public function getNegative_sign()
    {
        return $this->localeData['negative_sign'];
    }

    /**
     * @return the $positive_sign
     */
    public function getPositive_sign()
    {
        return $this->localeData['positive_sign'];
    }

    /**
     * @return the $mon_thousands_sep
     */
    public function getMon_thousands_sep()
    {
        return $this->localeData['mon_thousands_sep'];
    }

    /**
     * @return the $mon_decimal_point
     */
    public function getMon_decimal_point()
    {
        return $this->localeData['mon_decimal_point'];
    }

    /**
     * @return the $currency_symbol
     */
    public function getCurrency_symbol()
    {
        return $this->localeData['currency_symbol'];
    }

    /**
     * @return the $int_curr_symbol
     */
    public function getInt_curr_symbol()
    {
        return $this->localeData['int_curr_symbol'];
    }

    /**
     * @return the $thousands_sep
     */
    public function getThousands_sep()
    {
        return $this->localeData['thousands_sep'];
    }

    /**
     * @return the $decimal_point
     */
    public function getDecimal_point()
    {
        return $this->localeData['decimal_point'];
    }

    /**
     * @return the $language_direction
     */
    public function getLanguage_direction()
    {
        return $this->localeData['language_direction'];
    }

    /**
     * @return the $firstweekday
     */
    public function getFirstweekday()
    {
        return $this->localeData['firstweekday'];
    }

    /**
     * @return the $timeformat
     */
    public function getTimeformat()
    {
        return $this->localeData['timeformat'];
    }

    /**
     * @return the $grouping
     */
    public function getGrouping()
    {
        return $this->localeData['grouping'];
    }

    /**
     * @return the $mon_grouping
     */
    public function getMon_grouping()
    {
        return $this->localeData['mon_grouping'];
    }

    // automatic getters through ArrayAccess

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

