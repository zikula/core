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
 * StringUtil
 * @deprecated
 */
class StringUtil
{
    /**
     * Count the instances of needle in the given string
     *
     * Why is this function here? PHP has a builtin substr_count()
     * to do the same.
     *
     * @param string $haystack The string to search
     * @param string $needle   The needle to search for and count
     *
     * @return integer The numer of instances of needle in string
     */
    public static function countInstances($haystack, $needle)
    {
        return mb_substr_count($haystack, $needle);
    }

    /**
     * Truncate a string to a certain length
     *
     * @param string  $string     The string to operate on
     * @param integer $limit      The maximum number of characters displayed (optional) (default=80)
     * @param boolean $appendDots Whether or not to append '...' to the maximum number of characters displayed (optional) (default=80)
     *
     * @return string The potentially truncated string
     */
    public static function getTruncatedString($string, $limit = 80, $appendDots = true)
    {
        $len = strlen($string);

        if ($len > $limit) {
            $string = mb_substr($string, 0, $limit);

            if ($appendDots) {
                $string .= '...';
            }
        }

        return $string;
    }

    /**
     * Translate html input newlines to <br /> sequences.
     *
     * This function is necessary as inputted strings will contain
     * "\n\r" instead of just "\n".
     *
     * @param string $string The string to operate on
     *
     * @return string The converted string
     */
    public static function nl2html($string)
    {
        $str = str_replace("\n", '<br />', $string);
        $str = str_replace("\r", '', $str);

        return $str;
    }

    /**
     * Tokenize a string according to the given parameters.
     *
     * This function just wraps explode to provide a more java-similar syntax.
     *
     * @param string  $string    The string to tokenize
     * @param string  $delimeter The delimeter to use
     * @param integer $max       The maximal number of tokens to generate (optional) (default=999999)
     *
     * @return array The token array
     */
    public static function tokenize($string, $delimeter, $max = 999999)
    {
        return explode($delimeter, $string, $max);
    }

    /**
     * Case-Insensitive version of strpos (standard only available in PHP 5)
     *
     * @param string  $haystack The string to search
     * @param string  $needle   The string to search for
     * @param integer $offset   The search start offset position (optional) (default=0)
     *
     * @return array The token array
     */
    public static function stripos($haystack, $needle, $offset = 0)
    {
        return mb_strpos(mb_strtoupper($haystack), mb_strtoupper($needle), $offset);
    }

    /**
     * Returns the left x chars of a string.
     *
     * If the string is longer than x, the whole string is returned.
     *
     * @param string  $string The string to operate on
     * @param integer $left   The number of chars to return
     *
     * @return string A part of the supplied string
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
     * Returns the right x chars of a string.
     *
     * If the string is longer than x, the whole string is returned.
     *
     * @param string  $string The string to operate on
     * @param integer $right  The number of chars to return
     *
     * @return string A part of the supplied string
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
     *
     * Shorten text appropriate to view in hitlist.
     *
     * @param string  $text        The string to operate on
     * @param string  $wordStr     The search phrase
     * @param integer $contextSize The number of chars shown as context around the search phrase
     *
     * @return string A part of the supplied string
     */
    public static function highlightWords($text, $wordStr, $contextSize = 200)
    {
        // Strip HTML tags and special chars completely
        $text = strip_tags($text);
        $text = html_entity_decode($text, null, 'UTF-8');

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
            $startPos = (int) max(0, $startPos - floor($contextSize / 2));
            // Get the first word of section in full length
            while ($startPos > 0 && $text[$startPos] != ' ') {
                --$startPos;
            }

            // Get context on the right
            $endPos = (int) min($textLen, $startPos + $contextSize);
            // Get the last word of section in full length
            while ($endPos < (mb_strlen($text)) && $text[$endPos] != ' ') {
                ++$endPos;
            }

            // Setup section
            $section = mb_strcut($text, $startPos, $endPos - $startPos);
        } else {
            // Text is shorter than $contextSize
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

    /**
     * Camelize string.
     *
     * @param string $string    String to operate on
     * @param string $separator Seperator
     *
     * @return string
     */
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

    /**
     * Get Markdown Parser.
     *
     * @return Michelf\Markdown
     */
    public static function getMarkdownParser()
    {
        $sm = ServiceUtil::getManager();

        return $sm->get('zikula_core.common.markdown_parser');
    }

    /**
     * Get MarkdownExtra Parser.
     *
     * @return Michelf\MarkdownExtra
     */
    public static function getMarkdownExtraParser()
    {
        $sm = ServiceUtil::getManager();

        return $sm->get('zikula_core.common.markdown_extra_parser');
    }
}
