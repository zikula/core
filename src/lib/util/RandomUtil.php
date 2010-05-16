<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * RandomUtil
 *
 * @package Zikula_Core
 * @subpackage RandomUtil
 */
class RandomUtil
{
    /**
     * Return a seed value for the srand() function
     *
     * @return The resulting seed value
     */
    public static function getSeed()
    {
        $factor = 95717; // prime
        list ($usec, $sec) = explode(" ", microtime());
        return (double) strrev(($usec) * $factor / M_PI);
    }

    /**
     * Return a random integer between $floor and $ceil (inclusive)
     *
     * @param floor     The lower bound
     * @param ceil      The upper bound
     * @param seed      Whether or not to seed the random number generator (optional) (default=false) seeding not required for PHP>4.2.0
     *
     * @return The resulting random integer
     */
    public static function getInteger($floor, $ceil, $seed = false)
    {
        if ($seed) {
            srand(self::getSeed());
        }

        $diff = $ceil - $floor;

        // mr_rand seems to sometimes generate idential
        // series of random numbers. rand seems to do better.
        //$inc  = mt_rand (0, $diff);
        $inc = rand(0, $diff);

        return $floor + $inc;
    }

    /**
     * Return a random string of specified length. This function uses
     * uses md5() to generate the string.
     *
     * @param length    The length of string to generate
     * @param seed      Whether or not to seed the random number generator (optional) (default=false) seeding not required for PHP>4.2.0
     *
     * @return The resulting random integer
     */
    public static function getRandomString($length, $seed = true)
    {
        $res = '';

        if ($seed) {
            srand(self::getSeed());
        }

        while (strlen($res) < $length) {
            $res .= md5(self::getInteger(0, 100000));
        }

        return substr($res, 0, $length);
    }

    /**
     * Return a random string
     *
     * @param minLen    The minimum string length
     * @param maxLen    The maximum string length
     * @param leadingCapital Whether or not the string should start with a capital letter (optional) (default=true)
     * @param useUpper       Whether or not to also use uppercase letters (optional) (default=true)
     * @param useLower       Whether or not to also use lowercase letters (optional) (default=true)
     * @param useSpace       Whether or not to also use whitespace letters (optional) (default=true)
     * @param useNumber      Whether or not to also use numeric characters (optional) (default=false)
     * @param useSpecial     Whether or not to also use special characters (optional) (default=false)
     * @param seed           Whether or not to seed the random number generator (optional) (default=false) seeding not required for PHP>4.2.0
     * @param dontuse        Array of characters not to use (optional) (default=null) eg $dontuse=array('a', 'b', 'c');
     *
     * @return The resulting random string
     */
    public static function getString($minLen, $maxLen, $leadingCapital = true, $useUpper = true, $useLower = true, $useSpace = false, $useNumber = false, $useSpecial = false, $seed = false, $dontuse = null)
    {
        $rnd     = '';
        $chars   = '';
        $upper   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $lower   = "abcdefghijklmnopqrstuvwxyz";
        $number  = "0123456789";
        $special = "~@#$%^*()_+-={}|][";

        if ($seed) {
            srand(self::getSeed());
        }

        if ($useLower) {
            $chars .= $lower;
        }

        if ($useUpper) {
            $chars .= $upper;
        }

        if ($useNumber) {
            $chars .= $number;
        }

        if ($useSpecial) {
            $chars .= $special;
        }

        if ($useSpace) {
            for ($i = 0; $i < (strlen($chars) % 10); $i++) {
                $chars .= ' ';
            }
        }

        // omit the following characters
        if (!is_null($dontuse) && is_array($dontuse)) {
            $chars = str_replace($dontuse, '', $chars);
        }

        $len = self::getInteger($minLen, $maxLen);
        $clen = strlen($chars) - 1;
        for ($i = 0; $i < $len; $i++) {
            $rnd .= $chars[(self::getInteger(0, $clen))];
        }

        if ($leadingCapital) {
            $rnd = ucfirst($rnd);
        }

        return $rnd;
    }

    /**
     * Return a random sentence of nWords based on the dictionary
     *
     * @param nWords     The number of words to put in the sentence
     * @param dictArray  The array of dictionary words to use
     * @param seed       Whether or not to seed the random number generator (optional) (default=false) seeding not required for PHP>4.2.0
     *
     * @return The resulting random date string
     */
    public static function getSentence($nWords, $dictArray, $seed = false)
    {
        if (!$nWords) {
            return pn_exit(__('Invalid %1$s passed to %2$s.', array('nWords', 'RandomUtil::getSentence')));
        }

        if (!$dictArray) {
            return pn_exit(__('Invalid %1$s passed to %2$s.', array('dictArray', 'RandomUtil::getSentence')));
        }

        if ($seed) {
            srand(self::getSeed());
        }

        //$dictArray = explode (' ', $dict);
        $nDictWords = count($dictArray);
        $txt = '';

        $t = '';
        for ($i = 0; $i < $nWords; $i++) {
            $rnd = self::getInteger(0, $nDictWords);
            $word = $dictArray[$rnd];

            if ($i == 0) {
                $word = ucfirst($word);
            } else {
                $word = strtolower($word);
            }

            if (self::getInteger(0, 10) == 1 && $i < $nWords - 1 && !strpos($word, ',') && !strpos($word, '.')) {
                $word .= ', ';
            }

            if (strpos($word, '.') !== false) {
                $word = substr($word, 0, -1);
            }

            $t .= "$word ";
        }

        $txt .= substr($t, 0, -1) . '. ';
        return $txt;
    }

    /**
     * Return a nParas paragraphs of random text based on the dictionary
     *
     * @param nParas         The number of paragraphs to return to put in the sentence
     * @param dict           The dictionary to use (a space separated list of words)
     * @param irndS          The number of sentences in a paragraph (optional) (default=0=randomlyGenerated)
     * @param irndW          The number of words in a sentence (optional) (default=0=randomlyGenerated)
     * @param startCustomary Whether or not to start with the customary phrase (optional) (default=false)
     * @param seed           Whether or not to seed the random number generator (optional) (default=false) seeding not required for PHP>4.2.0
     *
     * @return The resulting random date string
     */
    public static function getParagraphs($nParas, $dict = '', $irndS = 0, $irndW = 0, $startCustomary = false, $seed = false)
    {
        if (!$nParas) {
            return pn_exit(__('Invalid %1$s passed to %2$s.', array('nParas', 'RandomUtil::getParagraphs')));
        }

        if (!$dict) {
            return pn_exit(__('Invalid %1$s passed to %2$s.', array('dictionary', 'RandomUtil::getParagraphs')));
        }

        if ($seed) {
            srand(self::getSeed());
        }

        $dictArray = explode(' ', $dict);
        $txt = '';
        for ($i = 0; $i < $nParas; $i++) {
            if (!$irndS) {
                $rndS = self::getInteger(3, 7);
            } else {
                $rndS = $irndS;
            }

            for ($j = 0; $j < $rndS; $j++) {
                if (!$irndW) {
                    $rndW = self::getInteger(8, 25);
                } else {
                    $rndW = $irndW;
                }
                $txt .= self::getSentence($rndW, $dictArray);
            }
            $txt .= "\n";
        }

        // start with first 5 words
        if ($startCustomary) {
            $pre = '';
            for ($i = 0; $i < 5; $i++) {
                $pre .= $dictArray[$i] . ' ';
            }
            $startLetter = substr($txt, 0, 1);
            $txt = $pre . strtolower($startLetter) . substr($txt, 1);
        }

        return $txt;
    }

    /**
     * Return a random date between $startDate and $endDate
     *
     * @param startDate  The lower date bound
     * @param endDate    The high date bound
     * @param format     The date format to use
     * @param seed       Whether or not to seed the random number generator (optional) (default=false) seeding not required for PHP>4.2.0
     *
     * @return The resulting random date string
     */
    public static function getDate($startDate, $endDate, $format = DATEFORMAT_FIXED, $seed = false)
    {
        if ($seed) {
            srand(self::getSeed());
        }

        $t1 = strtotime($startDate);
        $t2 = strtotime($endDate);

        $diff = $t2 - $t1;
        $inc = self::getInteger(0, $diff);

        $tRand = $t1 + $inc;
        Loader::loadClass('DateUtil');
        return DateUtil::getDatetime($tRand, $format);
    }

    /**
     * Return a random user-id
     *
     * @param seed      Whether or not to seed the random number generator (optional) (default=false) seeding not required for PHP>4.2.0
     *
     * @return The resulting random user-id
     */
    public static function getUserID($seed = false)
    {
        if ($seed) {
            srand(self::getSeed());
        }

        $fa = DBUtil::selectFieldArray('users', 'uid');
        $pos = self::getInteger(0, count($fa));
        return $fa[$pos];
    }

}
