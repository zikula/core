<?php
/**
* Zikula Application Framework
*
* @copyright (c) 2004, Zikula Development Team
* @link http://www.zikula.org
* @version $Id:  $
* @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* @package Zikula_Template_Plugins
* @subpackage Modifiers
*/

/**
* Smarty modifier to truncate a string preserving any html tag nesting and matching
*
* Original code from http://phpinsider.com/smarty-forum/viewtopic.php?t=533 
*   Author: Original Javascript Code: Benjamin Lupu <lupufr@aol.com>
*   Translation to PHP & Smarty: Edward Dale <scompt@scompt.com>
*   Modification to add a string: Sebastian Kuhlmann <sebastiankuhlmann@web.de>

* The plugin truncates a string preserving any html tag nesting and matching. The 
* string can be truncated on whole words or character. An optional suffix is added 
* when the string has been truncated.
*
* Example
*   <!--[$myvar|truncatehtml:100:'...']-->
* 
* @author       Erik Spaan [espaan]
* @since        11/12/2008
* @param        array    $string       The contents to transform
* @param        int      $length       The truncated string length in characters
* @param        string   $etc          Optional suffix that will only be added if the string is truncated (default empty)
* @param        bool     $break_words  Optional (default false)
* @return       string   the modified output
*/
function smarty_modifier_truncatehtml($string, $length, $etc='...', $break_words=false)
{
    if ($length == 0 && empty($string)) {
        return '';
    }

    // String length without html tags
    $noTagLength = strlen(strip_tags($string));
    if ($noTagLength > $length) {
        $isText = true;
        $ret = '';
        $i = 0;
        $currentChar = '';
        $lastSpacePosition = -1;
        $lastChar = '';
        $tagsArray = array();
        $currentTag = '';

        // Parser loop
        for ($j = 0; $j < strlen($string); $j++) {
            $currentChar = substr($string, $j, 1);
            $ret .= $currentChar;

            // Lesser than event
            if ($currentChar == '<') {
                $isText = false;
            }

            // Character handler
            if ($isText) {
                // Memorize last space position for wordwrap
                if ($currentChar == ' ') {
                    $lastSpacePosition = $j;
                } else {
                    $lastChar = $currentChar;
                }
                $i++;
            } else {
                $currentTag .= $currentChar;
            }

            // Greater than event
            if ($currentChar == '>') {
                $isText = true;
                // Opening tag handler
                if ((strpos($currentTag, '<') !== FALSE) &&
                        (strpos($currentTag, '/>') === FALSE) &&
                        (strpos($currentTag, '</') === FALSE)) {

                    // Tag has attribute(s)
                    if (strpos($currentTag, ' ') !== FALSE) {
                        $currentTag = substr($currentTag, 1, strpos($currentTag, ' ') - 1);
                    } else {
                        // Tag doesn't have attribute(s)
                        $currentTag = substr($currentTag, 1, -1);
                    }
                    // Put the tag in the array for restoring
                    array_push ($tagsArray, $currentTag);
                } elseif (strpos($currentTag, '</') !== FALSE) {
                    array_pop($tagsArray);
                }
                $currentTag = '';
            }
            if ($i >= $length) {
                break;
            }
        }

        // Cut HTML string at last space position
        if ($lastSpacePosition != -1 && !$break_words) {
            $ret = substr($string, 0, $lastSpacePosition);
        } else {
            $ret = substr($string, $j);
        }

        // Close broken XHTML elements
        while (sizeof($tagsArray) != 0) {
            $aTag = array_pop($tagsArray);
            $ret .= "</$aTag>\n";
        }
        // Add optional suffix
        if (!empty($etc)) {
            $ret .= ' ' . $etc;
        }

        return $ret;

    } else {
        // String not truncated
        return $string;
    }
}
