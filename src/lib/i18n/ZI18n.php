<?php
/**
 * Zikula Application Framework
 * @version $Id$
 *
 * Licensed to the Zikula Foundation under one or more contributor license
 * agreements. This work is licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 *
 * Please see the NOTICE and LICENSE files distributed with this source
 * code for further information regarding copyright ownership and licensing.
 */

/**
 * ZI18n class
 */
class ZI18n
{
    private static $instance;

    // public properties
    public $locale;
    private $sign;
    private $sign_posn;
    private $sep_by_space;
    private $cs_precedes;

    public function __construct($locale)
    {
        $this->locale = new ZLocale($locale);
    }

    public static function getInstance($locale=null)
    {
        if (!isset($locale)) {
            $locale = ZLanguage::getLanguageCode();
        }
        if (!isset(self::$instance[$locale])) {
            self::$instance[$locale] = new self($locale);
        }
        return self::$instance[$locale];
    }

    /**
     * transform a given currency into an internal number
     * @param $number
     * @return unknown_type
     */
    public function transformCurrencyInternal($number)
    {
        $number = (string)$number;
        $number = str_replace(' ', '', $number);
        $number = str_replace($this->locale['currency_symbol'], '', $number);
        $number = str_replace($this->locale['mon_thousands_sep'], '', $number);
        $number = str_replace($this->locale['Mon_decimal_point'], '.', $number);
        return (float)$number;
    }

    /**
     * transform a number into internal form with . as decimal
     *
     * @param $number
     * @return float
     */
    public function transformNumberInternal($number)
    {
        $number = (string)$number;
        $number = str_replace(' ', '', $number);
        $number = str_replace($this->locale['thousands_sep'], '', $number);
        $number = str_replace($this->locale['decimal_point'], '.', $number);
        return (float)$number;
    }

    /**
     * Format a number for display
     *
     * @param $number
     * @param $decimal_points null=default locale, false=precision, int=precision
     * @return unknown_type
     */
    public function transformNumberDisplay($number, $decimal_points=null)
    {
        $this->processSign($number);
        $decimal_points = (isset($decimal_points) ? $decimal_points : $this->locale['frac_digits']);
        if (!$decimal_points) {
            list($a, $b) = explode('.', $number);
            $decimal_points = strlen($b);
        }
        // Number format:
        $number = number_format(abs($number), $decimal_points, $this->locale['decimal_point'], $this->locale['thousands_sep']);

        switch ($this->sign_posn) {
            case 0:
                $number = "($number)";
                break;
            case 1:
                $number = "$this->sign{$number}";
                break;
            case 2:
                $number = "{$number}$this->sign";
                break;
            case 3:
                $number = "$this->sign{$number}";
                break;
            case 4:
                $number = "{$number}$this->sign";
                break;
            default:
                $number = "$number [error sign_posn=$this->sign_posn]";
        }

        return $number;
    }

    /**
     * format a number in monetary terms
     * @param $number
     * @return unknown_type
     */
    public function transformCurrencyDisplay($number)
    {
        $this->processSign($number);
        $number = number_format(abs($number), $this->locale['frac_digits'], $this->locale['mon_decimal_point'], $this->locale['mon_thousands_sep']);
        $space = ($this->sep_by_space ? ' ' : '');
        $number = $this->cs_precedes ? $this->locale['currency_symbol']."$space$number" : "$number$space".$this->locale['currency_symbol'];

        switch ($this->sign_posn) {
            case 0:
                $number = "($number)";
                break;
            case 1:
                $number = "$this->sign{$number}";
                break;
            case 2:
                $number = "{$number}$this->sign";
                break;
            case 3:
                $number = "$this->sign{$number}";
                break;
            case 4:
                $number = "{$number}$this->sign";
                break;
            default:
                $number = "$number [error sign_posn=$this->sign_posn]";
        }

        return $number;
    }


    /**
     * Process the positive or negative sign for a number
     * @param $number
     * @return unknown_type
     */
    public function processSign($number)
    {
        if ($number > 0) {
            $this->sign = $this->locale['Positive_sign'];
            $this->sign_posn = $this->locale['P_sign_posn'];
            $this->sep_by_space = $this->locale['P_sep_by_space'];
            $this->cs_precedes = $this->locale['P_cs_precedes'];
        } else {
            $this->sign = $this->locale['Negative_sign'];
            $this->sign_posn = $this->locale['N_sign_posn'];
            $this->sep_by_space = $this->locale['N_sep_by_space'];
            $this->cs_precedes = $this->locale['N_cs_precedes'];
        }
    }
}