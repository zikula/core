<?php
/* **************************************************************************************** */
/* Quick script to read a Zikula 1.2 type pntables.php file and convert it into a XML file. */
/* See pntables.dtd for a DTD format specification.                                         */
/*                                                                                          */
/* NOTE: if your pntables.php contains Zikula API calls (such as pnModGetVar, etc.) this    */
/* script WILL FAIL! In that case you should copy your pntables.php and replace such API    */
/* calls with hardcoded values which will then allow this script to process your pntables   */
/* file. Since presumably using this script to process your pntables.php is a one-time      */
/* process, this is IMHO an acceptable tradeoff in order to keep this script simple.        */
/*                                                                                          */
/* Usage Examples:                                                                          */
/*   pntableToXML.php: reads pntables.php, output goes to stdout                            */
/*   pntableToXML.php <inputfile>: reads <inputfile>, output goes to stdout                 */
/*   pntableToXML.php <inputfile> <outputfile>: reads <inputfile>, writes <outputfile>      *
/*                                                                                          */
/* Author: Robert Gasch (rgasch@gmail.com)                                                  */
/* **************************************************************************************** */


//
// process arguments, assign input default 
//
if (isset($argv[1])) {
    $fileIn = $argv[1];
} else {
    $fileIn = 'pntables.php';
}

$fileOut = null;
if (isset($argv[2])) {
    $fileOut = $argv[2];
}


//
// read file, split into lines
//
$lines = false;
$data  = file_get_contents($fileIn);
if (!$data) {
    exit ("Error reading file [$fileIn]\n");
} 
$lines   = explode("\n", $data);


//
// parse file: find modname so we can build a function name
//
$modName = null;
foreach ($lines as $line) {
    $line = str_replace ("\t", ' ', $line);
    $line = str_replace ('  ', ' ', $line);
    if (strpos ($line, 'function ') !== false && strpos ($line, '()') != false) {
        $fields  = explode (' ', $line);
        $nFields = count ($fields);
        if ($nFields < 2) {
            exit ("Expected at least 2 fields for function declaration but found [$nFields]\n");
        } 

        $fields  = explode ('_', $fields[1]);
        $modname = $fields[0];
        break;
    } 
} 
if (!$modname) {
    exit ("Unable to determine module name\n");
} 


//
// source php file, check for function existence, then call tables function to get tables data
//
require_once ($fileIn);
$funcName = $modname . '_pntables';
if (!function_exists($funcName)) {
    exit ("Function [$funcName] does not exist\n");
} 
$data = $funcName();


//
// iterate through tables data and build XML structure
//
$xml = new SimpleXMLElement('<tables></tables>');
foreach ($data as $table => $tableWithPrefix) {
    if (strpos($table, '_column') !== false || strpos($table, 'column_def') !== false || strpos ($table, 'column_idx') !== false) {
        continue;
    }

    // 
    // find primary key, categorization and attribution flags
    // 
    $primaryKeyColumn     = null;
    $enableAttribution    = false;
    $enableCategorization = false;
    foreach ($data as $k => $v) {
        if (strpos($k, "${table}_primary_key_column") !== false) {
            $primaryKeyColumn = $v;
        } 
        if (strpos($k, "${table}_db_extra_enable_categorization") !== false) {
            $enableCategorization = $v ? 'true' : 'false';
        } 
        if (strpos($k, "${table}_db_extra_enable_attribution") !== false) {
            $enableAttribution = $v ? 'true' : 'false';
        } 
    } 

    //
    // process and build XML
    //
    $columnArrayIdx = $table . '_column';
    $columnArrayDefIdx = $table . '_column_def';
    $columnArrayIdxIdx = $table . '_column_idx';
    if (!isset($data[$columnArrayIdx])) {
        continue;
    } 
    if (!isset($data[$columnArrayDefIdx])) {
        continue;
    } 

    $columnArray    = $data[$columnArrayIdx];
    $columnArrayDef = $data[$columnArrayDefIdx];
    $tableXML = $xml->addChild('table');
    $tableXML->addAttribute ('name', $table);
    if ($primaryKeyColumn) {
        $tableXML->addAttribute ('primaryKey', $primaryKeyColumn);
    } 
    $tableXML->addAttribute ('enableCategorization', $enableCategorization);
    $tableXML->addAttribute ('enableAttribution', $enableAttribution);

    foreach ($columnArray as $alias=>$dbColumn) {
        if (!isset($columnArrayDef[$alias])) { // skip calculated/math fields
           continue;
        } 
        $columnXML = $tableXML->addChild('column');
        $columnXML->addAttribute ('name', $alias);
        $columnXML->addAttribute ('dbname', $dbColumn);
        $type = ConversionUtil::parseType ($columnArrayDef[$alias]);
        $columnXML->addAttribute ('type', $type['type']);
        $columnXML->addAttribute ('length', $type['length']);
        $columnXML->addAttribute ('nullable', $type['nullable']);
        $columnXML->addAttribute ('default', $type['default']);
        $columnXML->addAttribute ('autoincrement', $type['autoincrement']);
        $columnXML->addAttribute ('primary', $type['primary']);
    } 

    if (isset($data[$columnArrayIdxIdx])) {
        $columnArrayIdxDef = $data[$columnArrayIdxIdx];
        foreach ($columnArrayIdxDef as $indexName => $indexDef) {
            if (is_array($indexDef)) {
                $t = array();
                foreach ($indexDef as $field) {
                    $t[] = $data[$columnArrayIdx][$field];
                } 
                $_indexDef = implode (',', $t);
                $columnXML = $tableXML->addChild('index');
                $columnXML->addAttribute ('name', $indexName);
                $columnXML->addAttribute ('fields', $_indexDef);
            } else {
                $columnXML = $tableXML->addChild('index');
                $columnXML->addAttribute ('name', $indexName);
                $columnXML->addAttribute ('fields', $indexDef);
            }
        } 
    } 
} 


//
// generate unformatted XML text, get rid of extra spaces
//
$xmlText = $xml->asXML();
$xmlText = preg_replace('/\s\s+/', ' ', $xmlText);


//
// generate formatted/readable XML text 
//
$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xmlText);
$xmlText = $dom->saveXML();


//
// save or echo results
//
if ($fileOut) {
    file_put_contents ($fileOut, $xmlText);
} else {
    print $xmlText;
}
exit();




/* ********************************************************* */
/* Dummy/Stub functions to allow processing of pntables file */
/* ********************************************************* */
class ConversionUtil 
{
    public static function parseType ($typeString) 
    { 
        $type       = array();
        $typeString = preg_replace('/\s\s+/', ' ', $typeString);
        $fields     = explode (' ', $typeString);
        $nFields    = count ($fields);

        $typemap    = array();
        $typemap['C']  = 'VARCHAR';
        $typemap['X']  = 'TEXT';
        $typemap['XL'] = 'LONGTEXT';
        $typemap['C2'] = 'VARCHAR';
        $typemap['X2'] = 'TEXT';
        $typemap['B']  = 'VARCHAR';
        $typemap['D']  = 'DATE';
        $typemap['T']  = 'DATETIME';
        $typemap['TS'] = 'DATETIME';
        $typemap['L']  = 'BOOLEAN';
        $typemap['I']  = 'INT';
        $typemap['I1'] = 'TINYINT';
        $typemap['I2'] = 'SMALLINT';
        $typemap['I3'] = 'MEDIUMINT';
        $typemap['I4'] = 'INT';
        $typemap['I8'] = 'BIGINT';
        $typemap['F']  = 'FLOAT';
        $typemap['N']  = 'NUMERIC';

        for ($c=0; $c<$nFields; $c++) {
            if ($c == 0) {
                if (strpos($fields[$c], '(') !== false) {
                    $pos1    = strpos ($fields[0], '(');
                    $pos2    = strpos ($fields[0], ')');
                    $fType   = substr ($fields[0], 0, $pos1);
                    $fType   = $typemap[$fType];
                    $fLength = substr ($fields[0], $pos1+1, $pos2-$pos1-1);
                    $type['type']   = $fType;
                    $type['length'] = $fLength;
                } else {
                    $type['type']   = $typemap[$fields[0]];
                    $type['length'] = null;
                }
            } else {
                if ($c == 1) {
                    if ($fields[$c] == 'NOTNULL') {
                        $type['nullable'] = 'false';
                    } else {
                        $type['nullable'] = 'true';
                    }
                }

                if ($fields[$c] == 'AUTO') {
                    $type['autoincrement'] = 'true';
                }

                if ($fields[$c] == 'PRIMARY') {
                    $type['primary'] = 'true';
                }

                if ($fields[$c] == 'DEFAULT') {
                    if ($nFields > $c+2) {
                        $type['default'] = $fields[$c+1] . ' ' . $fields[$c+2];
                        $c+=2;
                    } else {
                        $type['default'] = $fields[$c+1];
                        $c+=1;
                    }
                }
            }
        }

        if (!isset($type['nullable']))       { $type['nullable']      = 'true'; }
        if (!isset($type['autoincrement']))  { $type['autoincrement'] = 'false'; }
        if (!isset($type['default']))        { $type['default']       = ''; }
        if (!isset($type['primary']))        { $type['primary']       = 'false'; }

        return $type;
    }
}


class DBUtil 
{
    public static function getLimitedTablename ($table) 
    {
        return $table;
    } 
} 


class ObjectUtil 
{
    public static function addStandardFieldsToTableDefinition(&$columns, $col_prefix)
    {
        // ensure correct handling of prefix with and without underscore
        if ($col_prefix) {
            $plen = strlen($col_prefix);
            if ($col_prefix[$plen - 1] != '_')
                $col_prefix .= '_';
        }

        // add standard fields
        $columns['obj_status'] = $col_prefix . 'obj_status';
        $columns['cr_date']    = $col_prefix . 'cr_date';
        $columns['cr_uid']     = $col_prefix . 'cr_uid';
        $columns['lu_date']    = $col_prefix . 'lu_date';
        $columns['lu_uid']     = $col_prefix . 'lu_uid';

        return;
    }


    public static function addStandardFieldsToTableDataDefinition(&$columns)
    {
        $columns['obj_status'] = "C(1) NOTNULL DEFAULT 'A'";
        $columns['cr_date']    = "T NOTNULL DEFAULT '1970-01-01 00:00:00'";
        $columns['cr_uid']     = "I NOTNULL DEFAULT '0'";
        $columns['lu_date']    = "T NOTNULL DEFAULT '1970-01-01 00:00:00'";
        $columns['lu_uid']     = "I NOTNULL DEFAULT '0'";

        return;
    }
} 

