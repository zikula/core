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
 * Database tables for the search module
 *
 * This function is called internally by the core whenever the module is loaded. It adds in the information of the search module's database tables.
 */
function ZikulaSearchModule_tables()
{
    $search_stat = 'search_stat';

    $dbtable = [];
    $dbtable['search_stat'] = $search_stat;
    $dbtable['search_stat_column'] = [
        'id'     => 'id',
        'search' => 'search',
        'count'  => 'scount',
        'date'   => 'date'
    ];

    // column definitions
    $dbtable['search_stat_column_def'] = [
        'id'     => 'I4 PRIMARY AUTO',
        'search' => 'C(50) NOTNULL DEFAULT ""',
        'count'  => 'I4    NOTNULL DEFAULT "0"',
        'date'   => 'D     DEFDATE'
    ];

    $search_result = 'search_result';
    $dbtable['search_result'] = $search_result;
    $dbtable['search_result_column'] = [
        'id'      => 'id',
        'title'   => 'title',
        'text'    => 'text',
        'module'  => 'module',
        'extra'   => 'extra',
        'created' => 'created',
        'found'   => 'found',
        'session' => 'sesid'
    ];

    // column definitions
    $dbtable['search_result_column_def'] = [
        'id'      => 'I4 PRIMARY AUTO',
        'title'   => 'C(255) NOTNULL DEFAULT ""',
        'text'    => 'XL',
        'module'  => 'C(100)',
        'extra'   => 'C(1000)',
        'found'   => 'T DEFTIMESTAMP',
        'created' => 'T',
        'session' => 'C(50)'
    ];

    // additional indexes
    $dbtable['search_result_column_idx'] = [
        'title'  => 'title',
        'module' => 'module'
    ];

    return $dbtable;
}
