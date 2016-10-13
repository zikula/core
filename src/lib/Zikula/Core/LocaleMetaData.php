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
 * Class LocaleMetaData This class represents a locale with all its properties
 */
class LocaleMetaData
{
    /**
     * @var string local name
     */
    private $locale;

    /**
     * @var string ltr or rtl for left or right
     */
    private $language_direction = 'ltr';

    /**
     * @var string decimal point character
     */
    private $decimal_point = '.';

    /**
     * @var string thousands separator
     */
    private $thousands_sep = ',';

    /**
     * @var string international currency symbol (i.e. EUR)
     */
    private $int_curr_symbol = 'EUR';

    /**
     * @var string local currency symbol (i.e. €)
     */
    private $currency_symbol = '€';

    /**
     * @var string monetary decimal point character
     */
    private $mon_decimal_point = '.';

    /**
     * @var string monetary thousands separator
     */
    private $mon_thousands_sep = ',';

    /**
     * @var string sign for positive values
     */
    private $positive_sign = '';

    /**
     * @var string sign for negative values
     */
    private $negative_sign = '-';

    /**
     * @var int international fractional digits
     */
    private $int_frac_digits = 2;

    /**
     * @var int local fractional digits
     */
    private $frac_digits = 2;

    /**
     * @var bool TRUE if currency_symbol precedes a positive value, FALSE if it succeeds one
     */
    private $p_cs_precedes = true;

    /**
     * @var bool TRUE if a space separates currency_symbol from a positive value, FALSE otherwise
     */
    private $p_sep_by_space = true;

    /**
     * @var bool TRUE if currency_symbol precedes a negative value, FALSE if it succeeds one
     */
    private $n_cs_precedes = true;

    /**
     * @var bool TRUE if a space separates currency_symbol from a negative value, FALSE otherwise
     */
    private $n_sep_by_space = true;

    /**
     * @var int
     * 0 - parentheses surround the quantity and currency_symbol
     * 1 - The sign string precedes the quantity and currency_symbol
     * 2 - The sign string succeeds the quantity and currency_symbol
     * 3 - The sign string immediately precedes the currency_symbol
     * 4 - The sign string immediately succeeds the currency_symbol
     */
    private $p_sign_posn = 1;

    /**
     * @var int
     * 0 - parentheses surround the quantity and currency_symbol
     * 1 - The sign string precedes the quantity and currency_symbol
     * 2 - The sign string succeeds the quantity and currency_symbol
     * 3 - The sign string immediately precedes the currency_symbol
     * 4 - The sign string immediately succeeds the currency_symbol
     */
    private $n_sign_posn = 2;

    /**
     * @var int 0 = Sunday, 1 Monday etc
     */
    private $firstweekday = 0;

    /**
     * @var string Use 12/24 depending on country
     */
    private $timeformat = '24';

    /**
     * @var array An array containing numeric groupings
     */
    private $grouping = [];

    /**
     * @var array An array containing monetary groupings
     */
    private $mon_grouping = [];

    /**
     * LocaleMetaData constructor.
     * @param array $localeData
     */
    public function __construct(array $localeData)
    {
        foreach ($localeData as $key => $data) {
            if (property_exists($this, $key)) {
                $this->$key = $data;
            }
        }
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return integer
     */
    public function getN_sign_posn()
    {
        return $this->n_sign_posn;
    }

    /**
     * @return integer
     */
    public function getP_sign_posn()
    {
        return $this->p_sign_posn;
    }

    /**
     * @return integer
     */
    public function getN_sep_by_space()
    {
        return $this->n_sep_by_space;
    }

    /**
     * @return integer
     */
    public function getN_cs_precedes()
    {
        return $this->n_cs_precedes;
    }

    /**
     * @return integer
     */
    public function getP_sep_by_space()
    {
        return $this->p_sep_by_space;
    }

    /**
     * @return integer
     */
    public function getP_cs_precedes()
    {
        return $this->p_cs_precedes;
    }

    /**
     * @return integer
     */
    public function getFrac_digits()
    {
        return $this->frac_digits;
    }

    /**
     * @return integer
     */
    public function getInt_frac_digits()
    {
        return $this->int_frac_digits;
    }

    /**
     * @return string
     */
    public function getNegative_sign()
    {
        return $this->negative_sign;
    }

    /**
     * @return string
     */
    public function getPositive_sign()
    {
        return $this->positive_sign;
    }

    /**
     * @return string
     */
    public function getMon_thousands_sep()
    {
        return $this->mon_thousands_sep;
    }

    /**
     * @return string
     */
    public function getMon_decimal_point()
    {
        return $this->mon_decimal_point;
    }

    /**
     * @return string
     */
    public function getCurrency_symbol()
    {
        return $this->currency_symbol;
    }

    /**
     * @return string
     */
    public function getInt_curr_symbol()
    {
        return $this->int_curr_symbol;
    }

    /**
     * @return string
     */
    public function getThousands_sep()
    {
        return $this->thousands_sep;
    }

    /**
     * @return string
     */
    public function getDecimal_point()
    {
        return $this->decimal_point;
    }

    /**
     * @return string
     */
    public function getLanguage_direction()
    {
        return $this->language_direction;
    }

    /**
     * @return string
     */
    public function getFirstweekday()
    {
        return $this->firstweekday;
    }

    /**
     * @return string
     */
    public function getTimeformat()
    {
        return $this->timeformat;
    }

    /**
     * @return string
     */
    public function getGrouping()
    {
        return $this->grouping;
    }

    /**
     * @return string
     */
    public function getMon_grouping()
    {
        return $this->mon_grouping;
    }
}
