<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Populate dbtables array for securitycenter module.
 *
 * @return array dbtables array.
 */
function ZikulaSecurityCenterModule_tables()
{
    // Initialise table array
    $dbtable = array();

    // IDS intrusions table
    $dbtable['sc_intrusion'] = 'sc_intrusion';
    $dbtable['sc_intrusion_column'] = array('id'        => 'id',
                                            'name'      => 'name',
                                            'tag'       => 'tag',
                                            'value'     => 'value',
                                            'page'      => 'page',
                                            'uid'       => 'uid',
                                            'ip'        => 'ip',
                                            'impact'    => 'impact',
                                            'filters'   => 'filters',
                                            'date'      => 'date');

    $dbtable['sc_intrusion_column_def'] = array('id'        => 'I PRIMARY AUTO',
                                                'name'      => 'C(128) NOTNULL DEFAULT \'\'',
                                                'tag'       => 'C(40) DEFAULT NULL',
                                                'value'     => 'X NOTNULL',
                                                'page'      => 'X NOTNULL', // C(255)
                                                'uid'       => 'I4 DEFAULT NULL',
                                                'ip'        => 'C(40) NOTNULL DEFAULT \'\'', // C(15)
                                                'impact'    => 'I4 NOTNULL DEFAULT \'0\'',
                                                'filters'   => 'X NOTNULL',
                                                'date'      => 'T NOTNULL');

    // Return the table information
    return $dbtable;
}
