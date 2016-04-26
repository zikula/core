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
 * Sorts an array of named arrays by the supplied fields.
 *
 * Code by dholmes at jccc d0t net
 * taken from http://au.php.net/function.uasort
 * modified by cablehead, messju and pscs at http://www.phpinsider.com/smarty-forum
 *
 * @param array  &$data  The array to sort.
 * @param string $sortby Fields to sort by seperated by comma.
 *
 * @return void
 */
function array_sort_by_fields(&$data, $sortby)
{
    static $sort_funcs = [];

    if (empty($sort_funcs[$sortby])) {
        $code = "\$c=0;";
        foreach (explode(',', $sortby) as $key) {
            $d = '1';
            if (substr($key, 0, 1) == '-') {
                $d = '-1';
                $key = substr($key, 1);
            }
            if (substr($key, 0, 1) == '#') {
                $key = substr($key, 1);
                //  $code .= "if ( ( \$c = (\$a['$key'] - \$b['$key'])) != 0 ) return $d * \$c;\n";
                $code .= "if ( \$a['$key'] > \$b['$key']) return $d * 1;\n";
                $code .= "if ( \$a['$key'] < \$b['$key']) return $d * -1;\n";
            } else {
                $code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return $d * \$c;\n";
            }
        }
        $code .= 'return $c;';
        $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
    } else {
        $sort_func = $sort_funcs[$sortby];
    }

    uasort($data, $sort_func);
}

/**
 * Modifier: sortby - allows arrays of named arrays to be sorted by a given field.
 *
 * @param array  $arrData    The array to sort.
 * @param string $sortfields Fields to sort by seperated by comma.
 *
 * @return array
 */
function smarty_modifier_sortby($arrData, $sortfields)
{
    array_sort_by_fields($arrData, $sortfields);

    return $arrData;
}
