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
 * Internal search function.
 *
 * This function is called internally by the core whenever the module is loaded. It adds in the information of the search module's database tables.
 */
function Search_tables()
{
    $search_stat = DBUtil::getLimitedTablename('search_stat');

    $pntable = array();
    $pntable['search_stat'] = $search_stat;
    $pntable['search_stat_column'] = array ('id'     => 'pn_id',
                                            'search' => 'pn_search',
                                            'count'  => 'pn_count',
                                            'date'   => 'pn_date');

    // column definitions
    $pntable['search_stat_column_def'] = array ('id'     => 'I4 PRIMARY AUTO',
                                                'search' => 'C(50) NOTNULL DEFAULT ""',
                                                'count'  => 'I4    NOTNULL DEFAULT "0"',
                                                'date'   => 'D     DEFDATE');

    $search_result = DBUtil::getLimitedTablename('search_result');
    $pntable['search_result'] = $search_result;
    $pntable['search_result_column'] = array ('id'      => 'sres_id',
                                              'title'   => 'sres_title',
                                              'text'    => 'sres_text',
                                              'module'  => 'sres_module',
                                              'extra'   => 'sres_extra',
                                              'created' => 'sres_created',
                                              'found'   => 'sres_found',
                                              'session' => 'sres_sesid');

    // column definitions
    $pntable['search_result_column_def'] = array ('id'      => 'I4 PRIMARY AUTO',
                                                  'title'   => 'C(255) NOTNULL DEFAULT ""',
                                                  'text'    => 'XL',
                                                  'module'  => 'C(100)',
                                                  'extra'   => 'C(100)',
                                                  'found'   => 'T DEFTIMESTAMP',
                                                  'created' => 'T',
                                                  'session' => 'C(50)');

    // additional indexes
    $pntable['search_result_column_idx'] = array ('title'  => 'title',
                                                  'module' => 'module');


    return $pntable;
}
