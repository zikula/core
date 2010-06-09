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

function toggleWriteability(node, checked) {
    document.getElementById(node).disabled = checked;
}
