<?php
/**
 * Zikula_View modifier to convert theme type into a language string
 *
 * Example
 *
 *   <!--[$mythemetype|themetype]-->
 *
 * @see          modifier.varprepfordisplay.php::smarty_modifier_DataUtil::formatForDisplay()
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_themetype($string)
{
    switch ((int)$string) {
        case 3:
            return __('Theme 3.0');

    }
}
