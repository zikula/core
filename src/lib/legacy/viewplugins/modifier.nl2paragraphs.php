<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View modifier format the output of a comment
 *
 * The plugin compares the comment against the comment with all tags stipped
 * to determine of there is html content. If no html content is found then
 * each newline (\n) is converted to an close/open parapgraph pair </p><p>
 * lastly the output is wrapped in a paragraph (<p>content</p>) - this should
 * form valid html for a non formatted comment
 *
 * Example
 *
 *   {$myvar|nl2paragraphs}
 *
 * @param mixed $string The contents to transform.
 *
 * @return string The modified output.
 */
function smarty_modifier_nl2paragraphs($string)
{
    // compare the stipped version with original (identical means an unformated comment)
    if ($string == strip_tags($string)) {
        // strip all carriage returns (we're only interested in newlines)
        $string = str_replace("\r", '', $string);
        // replace newlines with a paragraph delimiter
        $string = str_replace("\n", '</p><p>', $string);
        // wrap string in a paragraph
        $string = '<p>' . $string . '</p>';
        // drop any empty parapgraphs
        $string = str_replace('<p></p>', '', $string);
    }

    return $string;
}
