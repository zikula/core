<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


/**
 * Populate tables array for modules module.
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @return       array       The table information.
 */
function theme_tables()
{
    // Initialise table array
    $dbtables = array();

    $dbtables['themes'] = DBUtil::getLimitedTablename('themes');
    $dbtables['themes_column'] = array ('id'             => 'pn_id',
                                       'name'           => 'pn_name',
                                       'type'           => 'pn_type',
                                       'displayname'    => 'pn_displayname',
                                       'description'    => 'pn_description',
                                       'regid'          => 'pn_regid',
                                       'directory'      => 'pn_directory',
                                       'version'        => 'pn_version',
                                       'official'       => 'pn_official',
                                       'author'         => 'pn_author',
                                       'contact'        => 'pn_contact',
                                       'admin'          => 'pn_admin',
                                       'user'           => 'pn_user',
                                       'system'         => 'pn_system',
                                       'state'          => 'pn_state',
                                       'credits'        => 'pn_credits',
                                       'changelog'      => 'pn_changelog',
                                       'help'           => 'pn_help',
                                       'license'        => 'pn_license',
                                       'xhtml'          => 'pn_xhtml');

    $dbtables['themes_column_def'] = array('id'          => "I PRIMARY AUTO",
                                          'name'        => "C(64) NOTNULL DEFAULT ''",
                                          'type'        => "I1 NOTNULL DEFAULT 0",
                                          'displayname' => "C(64) NOTNULL DEFAULT ''",
                                          'description' => "C(255) NOTNULL DEFAULT ''",
                                          'regid'       => "I NOTNULL DEFAULT 0",
                                          'directory'   => "C(64) NOTNULL DEFAULT ''",
                                          'version'     => "C(10) NOTNULL DEFAULT 0",
                                          'official'    => "I1 NOTNULL DEFAULT 0",
                                          'author'      => "C(255) NOTNULL DEFAULT ''",
                                          'contact'     => "C(255) NOTNULL DEFAULT ''",
                                          'admin'       => "I1 NOTNULL DEFAULT 0",
                                          'user'        => "I1 NOTNULL DEFAULT 0",
                                          'system'      => "I1 NOTNULL DEFAULT 0",
                                          'state'       => "I1 NOTNULL DEFAULT 0",
                                          'credits'     => "C(255) NOTNULL DEFAULT ''",
                                          'help'        => "C(255) NOTNULL DEFAULT ''",
                                          'changelog'   => "C(255) NOTNULL DEFAULT ''",
                                          'license'     => "C(255) NOTNULL DEFAULT ''",
                                          'xhtml'       => "I1 NOTNULL DEFAULT 1");

    return $dbtables;
}
