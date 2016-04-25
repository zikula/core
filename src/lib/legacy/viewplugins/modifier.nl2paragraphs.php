<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 *   {$myVar|nl2paragraphs}
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
