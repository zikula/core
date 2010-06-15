<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * StringUtil
 */
class StringUtil
{
    /**
     * Count the instances of needle in the given string
     *
     * Why is this function here? PHP has a builtin substr_count()
     * to do the same.
     *
     * @param haystack  the string to search
     * @param needle    the needle to search for and count
     *
     * @return The numer of instances of needle in string
     */
    public static function countInstances($haystack, $needle)
    {
        return mb_substr_count($haystack, $needle);
    }

    /**
     * Truncate a string to a certain length
     *
     * @param string       the string to operate on
     * @param limit        the maximum number of characters displayed (optional) (default=80)
     * @param appendDots   whether or not to append '...' to the maximum number of characters displayed (optional) (default=80)
     *
     * @return The potentially truncated string
     */
    public static function getTruncatedString($string, $limit = 80, $appendDots = true)
    {
        $len = strlen($string);

        if ($len > $limit) {
            $string = mb_substr($string, 0, $limit);

            if ($appendDots)
                $string .= '...';
        }

        return $string;
    }

    /**
     * Translate html input newlines to <br /> sequences.
     * This function is necessary as inputted strings will contain
     * "\n\r" instead of just "\n"
     *
     * @param string    the string to operate on
     *
     * @return The converted string
     */
    public static function nl2html($string)
    {
        $str = str_replace("\n", '<br />', $string);
        $str = str_replace("\r", '', $str);

        return $str;
    }

    /**
     * Tokenize a string according to the given parameters.
     * This function just wraps explode to provide a more java-similar syntax
     *
     * @param string     the string to tokenize
     * @param delimeter  the delimeter to use
     * @param max        the maximal number of tokens to generate (optional) (default=999999)
     *
     * @return The token array
     */
    public static function tokenize($string, $delimeter, $max = 999999)
    {
        return explode($delimeter, $string, $max);
    }

    /**
     * Case-Insensitive version of strpos (standard only available in PHP 5)
     *
     * @param haystack  the string to search
     * @param needle    the string to search for
     * @param offset    the search start offset position (optional) (default=0)
     *
     * @return The token array
     */
    public static function stripos($haystack, $needle, $offset = 0)
    {
        return mb_strpos(mb_strtoupper($haystack), mb_strtoupper($needle), $offset);
    }

    /**
     * Returns the left x chars of a string. If the string is longer than x,
     * the whole string is returned
     *
     * @param string       the string to operate on
     * @param left         the number of chars to return
     *
     * @return a part of the supplied string
     */
    public static function left($string, $left = 0)
    {
        $len = mb_strlen($string);
        if ($len > $left) {
            $string = mb_substr($string, 0, $left);
        }
        return $string;
    }

    /**
     * Returns the right x chars of a string. If the string is longer than x,
     * the whole string is returned
     *
     * @param string       the string to operate on
     * @param right        the number of chars to return
     *
     * @return a part of the supplied string
     */
    public static function right($string, $right = 0)
    {
        $len = mb_strlen($string);
        if ($len > $right) {
            $string = mb_substr($string, $len - $right, $right);
        }
        return $string;
    }

    /**
     * Markup text with highlight tags around search phrases.
     * Shorten text appropriate to view in hitlist.
     *
     * @param text         the string to operate on
     * @param wordStr      the search phrase
     * @param contextSize  the number of chars shown as context around the search phrase
     *
     * @return a part of the supplied string
     */
    public static function highlightWords($text, $wordStr, $contextSize = 200)
    {
        // Strip HTML tags and special chars completely
        $text = strip_tags($text);
        $text = html_entity_decode($text);

        // Split words into word array
        $words = preg_split('/ /', $wordStr, -1, PREG_SPLIT_NO_EMPTY);

        // Only shorten the text, if it is longer than contextSize
        $textLen = mb_strlen($text);
        if ($textLen > $contextSize) {
            // Find the very first position of all search phrases
            $startPos = $textLen;
            $foundStartPos = false;
            foreach ($words as $word) {
                $thisPos = mb_strpos($text, $word);
                if ($thisPos < $startPos) {
                    $startPos = $thisPos;
                    $foundStartPos = true;
                }
            }
            // No search phrase found
            if ($foundStartPos === false) {
                $startPos = 0;
            }

            // Get context on the left
            $startPos = max(0, $startPos - floor($contextSize / 2) );
            // Get the first word of section in full length
            while ($startPos > 0 && $text[$startPos] != ' ') {
                --$startPos;
            }

            // Get context on the right
            $endPos = min($textLen, $startPos + $contextSize);
            // Get the last word of section in full length
            while ($endPos < (mb_strlen($text)) && $text[$endPos] != ' ') {
                ++$endPos;
            }

            // Setup section 
            $section = mb_strcut($text, $startPos, $endPos-$startPos);

        } else { // Text is shorter than $contextSize
            $section = $text;
        }

        // Highlight search phrases within section
        if (($textLen <= $contextSize) || ($foundStartPos === true)) {
            $i = 1;
            foreach ($words as $word) {
                $section = str_replace($word, '<strong class="highlight' . ($i % 10) . '">' . $word . '</strong>', $section);
                ++$i;
            }
        }
        return $section;
    }

    public static function camelize($string, $separator = '_')
    {
        if (strpos($string, $separator) !== false) {
            $c = $string;
            $result = '';
            while (($p = strpos($c, '_')) !== false) {
                $result .= ucwords(substr($c, 0, $p));
                $c = substr($c, $p + 1);
            }
            $result .= ucwords($c);
        } else {
            $result = ucwords($string);
        }

        return $result;
    }
}
