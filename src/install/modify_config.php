<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

// Two global arrays
$reg_src = array();
$reg_rep = array();

// This is the last update to this script before the new version is finished.
// mod_file is general, give it a source file a destination.
// an array of search patterns (Perl style) and replacement patterns
// Returns a string which starts with "Err" if there's an error
function modify_file($reg_src, $reg_rep, $src='config/config.php')
{
    $in = @fopen($src, "r");
    if (!$in) {
        return __f('Error! Could not open \'%s\' for reading.', $src);
    }
    $i = 0;
    while (!feof($in)) {
        $file_buff1[$i++] = fgets($in, 4096);
    }
    fclose($in);

    $lines = 0; // Keep track of the number of lines changed

    while (list ($bline_num, $buffer) = each ($file_buff1)) {
        $new = preg_replace($reg_src, $reg_rep, $buffer);
        if ($new != $buffer) {
            $lines++;
        }
        $file_buff2[$bline_num] = $new;
    }

    if ($lines == 0) {
        // Skip the rest - no lines changed
        return __('Processed! But no lines were changed, so no action was taken.');
    }

    reset($file_buff2);
    $out_original = fopen($src, "w");
    if (! $out_original) {
        return __f('Error! Could not open \'%s\' for writing.', $src);
    } while (list ($bline_num, $buffer) = each ($file_buff2)) {
        fputs($out_original, $buffer);
    }

    fclose($out_original);
    // Success!
    return true;
}

// Setup various searches and replaces
// Scott Kirkwood
function add_src_rep($key, $rep)
{
    global $reg_src, $reg_rep;
    // Note: /x is to permit spaces in regular expressions
    // Great for making the reg expressions easier to read
    // Ex: $ZConfig['foo'] = stripslashes("bar");
    $reg_src[] = "/ \['$key'\] \s* = \s* stripslashes\( (\' | \") (.*) \\1 \); /x";
    $reg_rep[] = "['$key'] = stripslashes(\\1$rep\\1);";
    // Ex. $ZConfig['System']['tabletype']   = 'myisam';
    $reg_src[] = "/ \['$key'\] \s* = \s* (\' | \") (.*) \\1 ; /x";
    $reg_rep[] = "['$key'] = '$rep';";
    // Ex. $ZConfig['System']['development'] = 1;
    $reg_src[] = "/ \['$key'\] \s* = \s* (\d*\.?\d*) ; /x";
    $reg_rep[] = "['$key'] = $rep;";
}

function add_src_rep2($key, $rep)
{
    global $reg_src, $reg_rep;
    // Note: /x is to permit spaces in regular expressions
    // Great for making the reg expressions easier to read
    // Ex: $ZConfig['foo'] = stripslashes("bar");
    $reg_src[] = "/ \['default'\]\['$key'\] \s* = \s* stripslashes\( (\' | \") (.*) \\1 \); /x";
    $reg_rep[] = "['default']['$key'] = stripslashes(\\1$rep\\1);";
    // Ex. $ZConfig['System']['tabletype']   = 'myisam';
    $reg_src[] = "/ \['default'\]\['$key'\] \s* = \s* (\' | \") (.*) \\1 ; /x";
    $reg_rep[] = "['$key'] = '$rep';";
    // Ex. $ZConfig['System']['development'] = 1;
    $reg_src[] = "/ \['default'\]['$key'\] \s* = \s* (\d*\.?\d*) ; /x";
    $reg_rep[] = "['$key'] = $rep;";
}

// Update the config.php file with the database information.
function update_config_php($dbhost, $dbusername, $dbpassword, $dbname, $dbprefix, $dbtype, $dbtabletype)
{
    global $reg_src, $reg_rep;
    $dsn = "$dbtype://$dbusername:$dbpassword@$dbhost/$dbname";
    add_src_rep('dsn', $dsn);
    add_src_rep('prefix', $dbprefix);
    add_src_rep('dbtype', $dbtype);
    add_src_rep('dbtabletype', $dbtabletype);
    return modify_file($reg_src, $reg_rep);
}

function update_installed_status($state = '1')
{
    global $reg_src, $reg_rep;
    add_src_rep('installed', $state);
    return modify_file($reg_src, $reg_rep);
}
