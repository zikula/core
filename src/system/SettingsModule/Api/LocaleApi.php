<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Api;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;

class LocaleApi
{
    /**
     * @var bool has locale been loaded?
     */
    private $loaded = false;

    /**
     * @var string local name
     */
    private $locale = 'en';

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
     * @see \Zikula\SettingsModule\Listener\LocaleListener
     * @param $locale
     * @param $rootDir
     */
    public function load($locale, $rootDir)
    {
        if ($this->loaded) {
            return;
        }
        $this->locale = $locale;
        $fs = new Filesystem();
        $path = $rootDir . '/Resources/locale/' . $this->locale . '/locale.ini';
        if ($fs->exists($path)) {
            $this->parseIniFile($path);
        } else {
            throw new InvalidConfigurationException('Could not load the locale configuration.');
        }
        $this->loaded = true;
    }

    /**
     * Allows Twig to fetch properties without use of ArrayAccess
     *
     * ArrayAccess is problematic because Twig uses isset() to
     * check if property field exists, so it's not possible
     * to get using default values, ie, empty.
     *
     * @param $key
     * @param $args
     *
     * @return string
     */
    public function __call($key, $args)
    {
        if (!$this->loaded) {
            throw new InvalidConfigurationException('Did not load the locale configuration.');
        }

        return $this->$key;
    }

    /**
     * Parse an .ini file and set the object properties based on their current type.
     * @param $path
     */
    private function parseIniFile($path)
    {
        $iniFile = parse_ini_file($path, false);
        foreach ($iniFile as $key => $data) {
            if (property_exists($this, $key)) {
                switch (true) {
                    case is_bool($this->$key):
                        $this->$key = (bool) $data;
                        break;
                    case is_array($this->$key):
                        $this->$key = explode(',', $data);
                        break;
                    case is_int($this->$key):
                        $this->$key = (int) $data;
                        break;
                    default:
                        $this->$key = $data;
                }
            }
        }
    }
}
