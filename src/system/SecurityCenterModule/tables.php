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
