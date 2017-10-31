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
 * ZI18n class
 * @deprecated remove at Core-2.0
 */
class ZI18n
{
    /**
     * Singleton instance.
     *
     * @var ZI18n
     */
    private static $instance;

    /**
     * Locale.
     *
     * @var ZLocale
     */
    public $locale;

    /**
     * Sign.
     *
     * @var string
     */
    private $sign;

    /**
     * Sign position number.
     *
     * @var integer
     */
    private $sign_posn;

    /**
     * Whether or not to seperate currency symbol and number by space.
     *
     * @var boolean
     */
    private $sep_by_space;

    /**
     * Whether or not the currency symbol precedes.
     *
     * @var boolean
     */
    private $cs_precedes;

    /**
     * Constructor.
     *
     * @param ZLocale $locale Locale
     */
    public function __construct(ZLocale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get Singleton instance.
     *
     * @param string $locale Locale
     *
     * @return ZI18n object instance
     */
    public static function getInstance($locale = null)
    {
        if (!isset($locale)) {
            $locale = ZLanguage::getLanguageCode();
        }
        if (!isset(self::$instance[$locale])) {
            self::$instance[$locale] = new self(new ZLocale($locale));
        }

        return self::$instance[$locale];
    }

    /**
     * Transform a given currency into an internal number.
     *
     * @param string $number Number
     *
     * @return float
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
     * Transform a number into internal form with . as decimal.
     *
     * @param string $number Number
     *
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
     * Format a number for display.
     *
     * @param mixed $number         Number
     * @param mixed $decimal_points Null=default locale, false=precision, int=precision
     *
     * @return string
     */
    public function transformNumberDisplay($number, $decimal_points = null)
    {
        $this->processSign($number);
        $decimal_points = (isset($decimal_points) ? $decimal_points : $this->locale['frac_digits']);
        if (!$decimal_points && 0 !== $decimal_points) {
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
     * Format a number in monetary terms.
     *
     * @param mixed $number Number
     *
     * @return integer|string
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
     * Process the positive or negative sign for a number.
     *
     * @param mixed $number Number
     *
     * @return void
     */
    public function processSign($number)
    {
        if ($number >= 0) {
            $this->sign = $this->locale['positive_sign'];
            $this->sign_posn = $this->locale['p_sign_posn'];
            $this->sep_by_space = $this->locale['p_sep_by_space'];
            $this->cs_precedes = $this->locale['p_cs_precedes'];
        } else {
            $this->sign = $this->locale['negative_sign'];
            $this->sign_posn = $this->locale['n_sign_posn'];
            $this->sep_by_space = $this->locale['n_sep_by_space'];
            $this->cs_precedes = $this->locale['n_cs_precedes'];
        }
    }
}
