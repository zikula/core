/**
 * Zikula Application Framework
 * @version $Id$
 *
 * Licensed to the Zikula Foundation under one or more contributor license
 * agreements. This work is licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option any later version).
 *
 * Please see the NOTICE and LICENSE files distributed with this source
 * code for further information regarding copyright ownership and licensing.
 */
 
function CheckAll(formtype) {
    $$('.' + formtype + '_radio').each(function(el) { el.checked = $('toggle_' + formtype).checked;});
}

function CheckCheckAll(formtype) {
    var totalon = 0;
    $$('.' + formtype + '_radio').each(function(el) { if (el.checked) { totalon++; } });
    $('toggle_' + formtype).checked = ($$('.' + formtype + '_radio').length==totalon);
}
