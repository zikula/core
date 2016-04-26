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
 * Formats variable contents for display in the console.
 *
 * @param array|object $var    What is being modified.
 * @param integer      $depth  Depth to print arrays.
 * @param integer      $length Max length.
 *
 * @link http://smarty.php.net/manual/en/language.modifier.debug.print.var.php
 *
 * @return string
 */
function smarty_modifier_zdebug_print_var($var, $depth = 0, $length = 40)
{
    $_replace = [
        "\n" => '<i>\n</i>',
        "\r" => '<i>\r</i>',
        "\t" => '<i>\t</i>'
    ];

    if ($var instanceof Doctrine_Record || $var instanceof Doctrine_Collection) {
        $varname = get_class($var);
        $var = $var->toArray();
    }

    switch (gettype($var)) {
        case 'array':
            $results = '<strong>' . (isset($varname) ? "$varname Object" : 'Array') . ' (' . count($var) . ')</strong>';
            foreach ($var as $curr_key => $curr_val) {
                $results .= '<br>' . str_repeat('&nbsp;', $depth * 2)
                        . '<strong>' . strtr($curr_key, $_replace) . '</strong> =&gt; '
                        . smarty_modifier_debug_print_var($curr_val, ++$depth, $length);
                $depth--;
            }
            break;
        case 'object':
            $object_vars = get_object_vars($var);
            $results = '<strong>' . get_class($var) . ' Object (' . count($object_vars) . ')</strong>';
            foreach ($object_vars as $curr_key => $curr_val) {
                $results .= '<br>' . str_repeat('&nbsp;', $depth * 2)
                        . '<strong> -&gt;' . strtr($curr_key, $_replace) . '</strong> = '
                        . smarty_modifier_debug_print_var($curr_val, ++$depth, $length);
                $depth--;
            }
            break;
        case 'boolean':
        case 'NULL':
        case 'resource':
            if (true === $var) {
                $results = 'true';
            } elseif (false === $var) {
                $results = 'false';
            } elseif (null === $var) {
                $results = 'null';
            } else {
                $results = htmlspecialchars((string)$var);
            }
            $results = '<em>' . $results . '</em>';
            break;
        case 'integer':
        case 'float':
            $results = htmlspecialchars((string)$var);
            break;
        case 'string':
            $results = strtr($var, $_replace);
            if (strlen($var) > $length) {
                $results = substr($var, 0, $length - 3) . '...';
            }
            $results = htmlspecialchars('"' . $results . '"');
            break;
        case 'unknown type':
        default:
            $results = strtr((string)$var, $_replace);
            if (strlen($results) > $length) {
                $results = substr($results, 0, $length - 3) . '...';
            }
            $results = htmlspecialchars($results);
    }

    return $results;
}
