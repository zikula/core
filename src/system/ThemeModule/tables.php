<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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
 * Populate tables array for theme module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @return array The table information.
 */
function ZikulaThemeModule_tables()
{
    // Initialise table array
    $dbtables = array();

    $dbtables['themes'] = 'themes';
    $dbtables['themes_column'] = array('id'             => 'id',
                                       'name'           => 'name',
                                       'type'           => 'type',
                                       'displayname'    => 'displayname',
                                       'description'    => 'description',
                                       'directory'      => 'directory',
                                       'version'        => 'version',
                                       'contact'        => 'contact',
                                       'admin'          => 'admin',
                                       'user'           => 'user',
                                       'system'         => 'system',
                                       'state'          => 'state',
                                       'xhtml'          => 'xhtml');

    $dbtables['themes_column_def'] = array('id'          => "I PRIMARY AUTO",
                                          'name'        => "C(64) NOTNULL DEFAULT ''",
                                          'type'        => "I1 NOTNULL DEFAULT 0",
                                          'displayname' => "C(64) NOTNULL DEFAULT ''",
                                          'description' => "C(255) NOTNULL DEFAULT ''",
                                          'directory'   => "C(64) NOTNULL DEFAULT ''",
                                          'version'     => "C(10) NOTNULL DEFAULT 0",
                                          'contact'     => "C(255) NOTNULL DEFAULT ''",
                                          'admin'       => "I1 NOTNULL DEFAULT 0",
                                          'user'        => "I1 NOTNULL DEFAULT 0",
                                          'system'      => "I1 NOTNULL DEFAULT 0",
                                          'state'       => "I1 NOTNULL DEFAULT 0",
                                          'xhtml'       => "I1 NOTNULL DEFAULT 1");

    return $dbtables;
}
