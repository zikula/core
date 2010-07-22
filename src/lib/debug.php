<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Debug
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Exit.
 *
 * @param string  $msg  Message.
 * @param boolean $html True for html.
 *
 * @global array $ZConfig Configuration.
 * @return void|boolean
 */
function z_exit($msg, $html = true)
{
    return LogUtil::registerError($msg2);
}


/**
 * Serialize the given data in an easily human-readable way for debug purposes.
 *
 * Taken from http://dev.nexen.net/scripts/details.php?scripts=707.
 *
 * @param array   $data           The object to serialize.
 * @param boolean $functions      Whether to show function names for objects (default=false) (optional).
 * @param integer $recursionLevel The current recursion level.
 *
 * @return string A string containing serialized data.
 */
function _prayer($data, $functions = false, $recursionLevel = 0)
{
//    if ($recursionLevel > 5) {
//        return __('Maximum recursion level reached');
//    }
//
//    global $ZConfig;
//    if (System::isInstalling() && !$ZConfig['System']['development']) {
//        return;
//    }
//
//    $text = '';
//
//    if ($functions != 0) {
//        $sf = 1;
//    } else {
//        $sf = 0;
//    }
//
//    if (isset($data)) {
//        if (is_array($data) || is_object($data)) {
//            $datatype = gettype($data);
//            if (count($data)) {
//                $text .= "<ol>\n";
//
//                foreach ($data as $key => $value) {
//                    $type = gettype($value);
//
//                    if ($type == 'array' || ($type == 'object' && get_object_vars($value))) {
//                        $text .= sprintf("<li>(%s) <strong>%s</strong>:\n", $type, $key);
//                        $text .= _prayer($value, $sf, $recursionLevel + 1);
//                        $text .= '</li>';
//
//                    } elseif (preg_match('/function/i', $type)) {
//                        if ($sf) {
//                            $text .= sprintf("<li>(%s) <strong>%s</strong> </li>\n", $type, $key, $value);
//                            // There doesn't seem to be anything traversable inside functions.
//                        }
//                    } else {
//                        if (!isset($value)) {
//                            $value = '(none)';
//                        }
//
//                        // You cannot do DataUtil::formatForDisplay on an object, so just display object type
//                        if (is_object($value)) {
//                            $value = gettype($value);
//
//                        } elseif (is_bool($value)) {
//                            $value = (int)$value;
//                        }
//
//                        // parse th eoutput
//                        if ($datatype == 'array') {
//                            $text .= sprintf("<li>(%s) <strong>%s</strong> = %s</li>\n", $type, $key, DataUtil::formatForDisplay($value));
//
//                        } elseif ($datatype == 'object') {
//                            $text .= sprintf("<li>(%s) <strong>%s</strong> -> %s</li>\n", $type, $key, DataUtil::formatForDisplay($value));
//                        }
//                    }
//                }
//
//                $text .= "</ol>\n";
//            } else {
//                $text .= '(empty)';
//            }
//        } else {
//            $text .= $data;
//        }
//    }
//    return $text;
}


/**
 * A prayer shortcut.
 *
 * @param array   $data The object to serialize.
 * @param boolean $die  Whether to shutdown the process or not.
 *
 * @return void
 */
function z_prayer($data, $die = true)
{
//    echo _prayer($data);
//
//    if ($die) {
//        System::shutdown();
//    }
}

/**
 * Serialize the given data in an easily human-readable way for debug purposes.
 *
 * Taken from http://dev.nexen.net/scripts/details.php?scripts=707.
 *
 * @param array   $data      The object to serialize.
 * @param boolean $functions Whether to show function names for objects (default=false) (optional).
 *
 * @return void
 */
function prayer($data, $functions = false)
{
//    global $ZConfig;
//    if (System::isInstalling() && !$ZConfig['System']['development']) {
//        return;
//    }
//
//    $text = '<div style="text-align:left">';
//    $text .= _prayer($data, $functions);
//    $text .= '</div>';
//    print($text);
}


