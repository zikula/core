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
 * Smarty modifier to remove censored words
 *
 * This modifier examines the contents of the passed variable for words which
 * are deemed offensive or otherwise not allowed to be displayed. These words
 * are replaced with asterix marks to show that words have been removed.
 *
 * This modifier tries to be intelligent in its attempt to remove censored
 * words whilst not censoring words on the censor list that happen to be
 * embedded in a larger word.
 *
 * This modifier uses the information provided in the configuration setting
 * 'CensorList' as the basis of the words that it censors. It also looks for
 * commonly derivations of the words used to try to avoid censoring. The system
 * is also case-insensitive.
 *
 * Care should be taken to consider the effect of censorship, and if it should
 * be applied to all information that is passed in by the user or if it should
 * only be used in specific cases.
 *
 * This modifier is to be removed in future versions, as pnVarCensor is being moved
 * to be a transform hook.
 *
 * Example
 *
 *   {$MyVar|varcensor}
 *
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_varcensor($string)
{
    LogUtil::log(__f('Warning! Template modifier {$var|%1$s} is deprecated.', array('varcensor')), E_USER_DEPRECATED);

    return pnVarCensor($string);
}
