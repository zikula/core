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
 * ZLocale class
 * @deprecated remove at Core-2.0
 */
class ZLocale implements ArrayAccess
{
    // public properties
    /**
     * Locale.
     *
     * @var string
     */
    private $locale;

    /**
     * Errors.
     *
     * @var array
     */
    private $errors = [];

    /**
     * Locale data.
     *
     * @var array
     */
    private $localeData = [
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
        'grouping' => [],
        'mon_grouping' => []
    ];

    /**
     * Constructor.
     *
     * @param string $locale Loacle
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
        $this->loadLocaleConfig();
        $this->detectErrors();
    }

    /**
     * Load locale config
     *
     * @return void
     */
    private function loadLocaleConfig()
    {
        $lang = ZLanguage::transformFS($this->locale);
        $override = "config/locale/$lang/locale.ini";
        $file = (file_exists($override) ? $override : "app/Resources/locale/$lang/locale.ini");
        if (is_readable($file)) {
            $array = parse_ini_file($file, false);
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
            //$this->registerError(__f("Error! Could not load '%s'. Please check that it exists.", $file));
        }
    }

    /**
     * Validate the locale.
     *
     * @param string $file Locale file
     *
     * @return void
     */
    private function validateLocale($file)
    {
        if (count($this->localeData) == 0) {
            $this->registerError(__f('Error! The locale file %s contains invalid data.', [$file]));

            return;
        }
        $validationArray = ['language_direction' => '#^(ltr|rtl)$#'];
        foreach ($validationArray as $key => $validation) {
            if (!isset($this->localeData[$key])) {
                $this->registerError(__f('Error! %1$s is missing in %2$s.', [$key, $file]));
            } else {
                if (!preg_match($validation, $this->localeData[$key])) {
                    $this->registerError(__f('Error! There is an invalid value for %1$s in %2$s.', [$key, $file]));
                }
            }
        }
    }

    /**
     * Register an error.
     *
     * @param string $msg Error message
     *
     * @return void
     */
    private function registerError($msg)
    {
        $this->errors[] = $msg;
    }

    /**
     * Detect errors.
     *
     * @return boolean
     */
    private function detectErrors()
    {
        if (count($this->errors) == 0) {
            return true;
        }

        // fatal errors require 404
        header('HTTP/1.1 404 Not Found');
        foreach ($this->errors as $error) {
            LogUtil::addErrorPopup($error);
        }

        return false;
    }

    // getters

    /**
     * Get n_sign_posn.
     *
     * @return integer The $n_sign_posn
     */
    public function getN_sign_posn()
    {
        return $this->localeData['n_sign_posn'];
    }

    /**
     * Get p_sign_posn.
     *
     * @return integer The $p_sign_posn
     */
    public function getP_sign_posn()
    {
        return $this->localeData['p_sign_posn'];
    }

    /**
     * Get n_sep_by_space.
     *
     * @return integer The $n_sep_by_space
     */
    public function getN_sep_by_space()
    {
        return $this->localeData['n_sep_by_space'];
    }

    /**
     * Get n_cs_precedes.
     *
     * @return integer The $n_cs_precedes
     */
    public function getN_cs_precedes()
    {
        return $this->localeData['n_cs_precedes'];
    }

    /**
     * Get p_sep_by_space.
     *
     * @return integer The $p_sep_by_space
     */
    public function getP_sep_by_space()
    {
        return $this->localeData['p_sep_by_space'];
    }

    /**
     * Get p_cs_precedes.
     *
     * @return integer The $p_cs_precedes
     */
    public function getP_cs_precedes()
    {
        return $this->localeData['p_cs_precedes'];
    }

    /**
     * Get frac_digets.
     *
     * @return integer The $frac_digits
     */
    public function getFrac_digits()
    {
        return $this->localeData['frac_digits'];
    }

    /**
     * Get int_frac_digits.
     *
     * @return integer The $int_frac_digits
     */
    public function getInt_frac_digits()
    {
        return $this->localeData['int_frac_digits'];
    }

    /**
     * Get negative_sign.
     *
     * @return string The $negative_sign
     */
    public function getNegative_sign()
    {
        return $this->localeData['negative_sign'];
    }

    /**
     * Get positive_sign.
     *
     * @return string The $positive_sign
     */
    public function getPositive_sign()
    {
        return $this->localeData['positive_sign'];
    }

    /**
     * Get mon_thousands_sep.
     *
     * @return string The $mon_thousands_sep
     */
    public function getMon_thousands_sep()
    {
        return $this->localeData['mon_thousands_sep'];
    }

    /**
     * Get mon_decimal_point.
     *
     * @return string The $mon_decimal_point
     */
    public function getMon_decimal_point()
    {
        return $this->localeData['mon_decimal_point'];
    }

    /**
     * Get currency_symbol.
     *
     * @return string The $currency_symbol
     */
    public function getCurrency_symbol()
    {
        return $this->localeData['currency_symbol'];
    }

    /**
     * Get int_curr_symbol.
     *
     * @return string The $int_curr_symbol
     */
    public function getInt_curr_symbol()
    {
        return $this->localeData['int_curr_symbol'];
    }

    /**
     * Get thousands_sep.
     *
     * @return string The $thousands_sep
     */
    public function getThousands_sep()
    {
        return $this->localeData['thousands_sep'];
    }

    /**
     * Get decimal_point.
     *
     * @return string The $decimal_point
     */
    public function getDecimal_point()
    {
        return $this->localeData['decimal_point'];
    }

    /**
     * Get language_direction.
     *
     * @return string The $language_direction
     */
    public function getLanguage_direction()
    {
        return $this->localeData['language_direction'];
    }

    /**
     * Get firstweekday.
     *
     * @return string The $firstweekday
     */
    public function getFirstweekday()
    {
        return $this->localeData['firstweekday'];
    }

    /**
     * Get timeformat.
     *
     * @return strng The $timeformat
     */
    public function getTimeformat()
    {
        return $this->localeData['timeformat'];
    }

    /**
     * Get grouping.
     *
     * @return string The $grouping
     */
    public function getGrouping()
    {
        return $this->localeData['grouping'];
    }

    /**
     * Get mon_grouping.
     *
     * @return string The $mon_grouping
     */
    public function getMon_grouping()
    {
        return $this->localeData['mon_grouping'];
    }

    // automatic getters through ArrayAccess

    /**
     * Whether or not the offset exists.
     *
     * @param string $offset The offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get by offset.
     *
     * @param string $offset The offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->localeData[$offset];
    }

    /**
     * Set by offset.
     *
     * @param string $offset The offset
     * @param mixed  $value  The value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->localeData[$offset] = $value;
    }

    /**
     * Unset by offset.
     *
     * @param string $offset The offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->localeData[$offset]);
    }
}
