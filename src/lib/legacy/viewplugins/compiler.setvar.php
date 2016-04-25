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
 * Assign a value to a variable, also arrays or objects.
 *
 * Available attributes:
 *  - var   (mixed)  Name or variable to assign.
 *  - value (mixed)  Value to assign.
 *
 * Examples:
 *
 *  Initialize an empty array.
 *
 *  <samp>{setvar var='foo' php='array()'}</samp>
 *
 *  Set an array item.
 *
 *  <samp>{setvar var='foo.bar' value=$myValue}</samp>
 *
 *  Set a variable array field.
 *
 *  <samp>{setvar var="foo.$key" value=$myValue}</samp>
 *
 *  Set an object property.
 *
 *  <samp>{setvar var='obj->bar' value=$myValue}</samp>
 *
 *  Creates a new indexed array or fill the key-values in a existing $foo array.
 *
 *  <samp>{setvar var='foo' keys='a,b,c' values='1,2,3'}</samp>
 *
 *  Using another delimiter.
 *
 *  <samp>{setvar var='bar' keys='a-b-c' values='1-2-3' delim='-'}</samp>
 *
 * @param array           $params   All attributes passed to this function from the template.
 * @param Smarty_Compiler $compiler Reference to the {@link Zikula_View} object.
 *
 * @return void
 */
function smarty_compiler_setvar($params, Smarty_Compiler $compiler)
{
    /* extract the var parameter */
    if (preg_match('!(.*)var=('.$compiler->_qstr_regexp.'|\S+?)(.*?)!Us', $params, $_match)) {
        $_lval  = $_match[2];
        $_attrs = $compiler->_parse_attrs($_match[1] . $_match[3]);
    } else {
        $compiler->_syntax_error(__f("Missing or invalid '%s' parameter.", 'var'), E_USER_WARNING, __FILE__, __LINE__);

        return;
    }

    /* test a possible variable */
    $_lval_var = $compiler->_parse_var_props('$'.$compiler->_dequote($_lval));

    if ($_lval_var{0} == '$') {
        $_lval = $_lval_var;
    } else {
        /* take it as a variable-name */
        $_lval = '$this->_tpl_vars['.$compiler->_parse_var_props($_lval).']';
    }

    /* check for a PHP command as value */
    if (isset($_attrs['php'])) {
        // do not allow more than one PHP command
        if (strpos($_attrs['php'], ';') !== false) {
            $_attrs['php'] = substr($_attrs['php'], 0, strpos($_attrs['php'], ';'));
        }
        $_rval = $compiler->_dequote($_attrs['php']);

    /* check for a scalar the value parameter */
    } elseif (isset($_attrs['value'])) {
        $_rval = $_attrs['value'];

    /* list of array-values */
    } elseif (isset($_attrs['values'])) {
        $_delim = (isset($_attrs['delim'])) ? $_attrs['delim'] : "','";
        $_rval = 'explode('.$_delim.', '.$_attrs['values'].')';

        /* if indexed */
        if (isset($_attrs['keys'])) {
            if (substr_count($_attrs['keys'], $_delim) != substr_count($_attrs['values'], $_delim)) {
                $compiler->_syntax_error(__("Keys and values size doesn't match."), E_USER_WARNING, __FILE__, __LINE__);

                return;
            }

            $_code = "\$_values = $_rval; ".
                     "if (!isset($_lval)) { $_lval = array(); } "
                     ."foreach (explode($_delim, ".$_attrs['keys'].") as \$_i => \$_key) { "
                     ."    ${_lval}[\$_key] = \$_values[\$_i]; "
                     ."}";

            return $_code;
        }
    } else {
        $compiler->_syntax_error(__f("Missing '%s' parameter.", 'value'), E_USER_WARNING, __FILE__, __LINE__);

        return;
    }

    return $_lval.'='.$_rval.';';
}
