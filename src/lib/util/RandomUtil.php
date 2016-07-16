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
 * RandomUtil
 */
class RandomUtil
{
    /**
     * Return a seed value for the srand() function
     *
     * @deprecated Since 1.3.0, as this is not required since PHP 4.2.0
     *
     * @return The resulting seed value
     */
    public static function getSeed()
    {
        $factor = 95717; // prime
        list($usec, $sec) = explode(" ", microtime());

        return (float)strrev(($usec) * $factor / M_PI);
    }

    /**
     * Return a random integer between $floor and $ceil (inclusive).
     *
     * @param integer $floor The lower bound
     * @param integer $ceil  The upper bound
     *
     * @return The resulting random integer
     */
    public static function getInteger($floor, $ceil)
    {
        $diff = $ceil - $floor;

        $inc = mt_rand(0, $diff);

        return $floor + $inc;
    }

    /**
     * Return a random string of specified length.
     *
     * This function uses md5() to generate the string.
     *
     * @param integer $length The length of string to generate
     *
     * @return The resulting random integer
     */
    public static function getRandomString($length)
    {
        $res = '';

        while (strlen($res) < $length) {
            $res .= md5(self::getInteger(0, 100000));
        }

        return substr($res, 0, $length);
    }

    /**
     * Return a random string
     *
     * @param integer $minLen         The minimum string length
     * @param integer $maxLen         The maximum string length
     * @param boolean $leadingCapital Whether or not the string should start with a capital letter (optional) (default=true)
     * @param boolean $useUpper       Whether or not to also use uppercase letters (optional) (default=true)
     * @param boolean $useLower       Whether or not to also use lowercase letters (optional) (default=true)
     * @param boolean $useSpace       Whether or not to also use whitespace letters (optional) (default=true)
     * @param boolean $useNumber      Whether or not to also use numeric characters (optional) (default=false)
     * @param boolean $useSpecial     Whether or not to also use special characters (optional) (default=false)
     * @param boolean $seed           Whether or not to seed the random number generator (unused since 1.3.0) (optional) (default=false) seeding not required for PHP>4.2.0
     * @param array   $dontuse        Array of characters not to use (optional) (default=null) eg $dontuse = ['a', 'b', 'c'];
     *
     * @return The resulting random string
     */
    public static function getString($minLen, $maxLen, $leadingCapital = true, $useUpper = true, $useLower = true, $useSpace = false, $useNumber = false, $useSpecial = false, $seed = false, $dontuse = null)
    {
        $rnd = '';
        $chars = '';
        $upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $lower = "abcdefghijklmnopqrstuvwxyz";
        $number = "0123456789";
        $special = "~@#$%^*()_+-={}|][";

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
     * Return a random string suitable for use as a password or password-like code.
     *
     * The string should conform to the constraints of the current password requirements:
     * suitable for human use (readable and unambiguous), within the specified minimum and maximum lengths.
     *
     * @param integer $minLength The minimum length of the string to return; optional; default = 5; constrained to 1 <= $minLength <= 25
     * @param integer $maxLength The maximum length of the string to return; optional; default = $minLength; constrained to $minLength <= $maxLength <= 25
     *
     * @return string|bool A random string suitable for human-use as a password or password-like code; false on error
     */
    public static function getStringForPassword($minLength = 5, $maxLength = null)
    {
        if (!is_numeric($minLength) || ((int)$minLength != $minLength) || ($minLength <= 0)) {
            return false;
        }
        $minLength = min($minLength, 25);

        if (!isset($maxLength)) {
            $maxLength = $minLength;
        } elseif (!is_numeric($maxLength) || ((int)$maxLength != $maxLength) || ($maxLength <= 0)) {
            return false;
        } elseif ($maxLength <= $minLength) {
            $maxLength = $minLength;
        } else {
            $maxLength = min($maxLength, 25);
        }

        return self::getString($minLength, $maxLength, false, false, true, false, true, false, false, ['0', 'o', 'O', 'l', '1', 'i', 'I', 'j', '!', '|']);
    }

    /**
     * Return a random sentence of nWords based on the dictionary
     *
     * @param integer $nWords    The number of words to put in the sentence
     * @param array   $dictArray The array of dictionary words to use
     *
     * @return The resulting random date string
     */
    public static function getSentence($nWords, $dictArray)
    {
        if (!$dictArray) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['dictArray', 'RandomUtil::getSentence']));
        }

        if (!$nWords) {
            $nWords = self::getInteger(5, 10);
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
     * Return a nParas paragraphs of random text based on the dictionary.
     *
     * @param integer $nParas         The number of paragraphs to return to put in the sentence
     * @param string  $dict           The dictionary to use (a space separated list of words)
     * @param integer $irndS          The number of sentences in a paragraph (optional) (default=0=randomlyGenerated)
     * @param integer $irndW          The number of words in a sentence (optional) (default=0=randomlyGenerated)
     * @param boolean $startCustomary Whether or not to start with the customary phrase (optional) (default=false)
     *
     * @return The resulting random date string
     */
    public static function getParagraphs($nParas, $dict = '', $irndS = 0, $irndW = 0, $startCustomary = false)
    {
        if (!$dict) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['dictionary', 'RandomUtil::getParagraphs']));
        }

        if (!$nParas) {
            $nParas = self::getInteger(3, 7);
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
     * Return a random date between $startDate and $endDate.
     *
     * @param string $startDate The lower date bound
     * @param string $endDate   The high date bound
     * @param string $format    The date format to use
     *
     * @return The resulting random date string
     */
    public static function getDate($startDate, $endDate, $format = DATEFORMAT_FIXED)
    {
        $t1 = strtotime($startDate);
        $t2 = strtotime($endDate);

        $diff = $t2 - $t1;
        $inc = self::getInteger(0, $diff);

        $tRand = $t1 + $inc;

        return DateUtil::getDatetime($tRand, $format);
    }

    /**
     * Return a random user-id.
     *
     * @return The resulting random user-id
     */
    public static function getUserID()
    {
        $fa = DBUtil::selectFieldArray('users', 'uid');
        $pos = self::getInteger(0, count($fa));

        return $fa[$pos];
    }
}
