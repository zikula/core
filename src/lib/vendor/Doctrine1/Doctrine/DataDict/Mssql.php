<?php
/*
 *  $Id: Mssql.php 7660 2010-06-08 18:30:22Z jwage $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

/**
 * @package     Doctrine
 * @subpackage  DataDict
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Lukas Smith <smith@pooteeweet.org> (PEAR MDB2 library)
 * @author      Frank M. Kromann <frank@kromann.info> (PEAR MDB2 Mssql driver)
 * @author      David Coallier <davidc@php.net> (PEAR MDB2 Mssql driver)
 * @version     $Revision: 7660 $
 * @link        www.doctrine-project.org
 * @since       1.0
 */
class Doctrine_DataDict_Mssql extends Doctrine_DataDict
{
    /**
     * Obtain DBMS specific SQL code portion needed to declare an text type
     * field to be used in statements like CREATE TABLE.
     *
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      length
     *          Integer value that determines the maximum length of the text
     *          field. If this argument is missing the field should be
     *          declared to have the longest length allowed by the DBMS.
     *
     *      default
     *          Text value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     *
     * @return      string      DBMS specific SQL code portion that should be used to
     *                          declare the specified field.
     */
    public function getNativeDeclaration($field)
    {
        if ( ! isset($field['type'])) {
            throw new Doctrine_DataDict_Exception('Missing column type.');
        }
        switch ($field['type']) {
            case 'enum':
                $field['length'] = isset($field['length']) && $field['length'] ? $field['length']:255;
            case 'array':
            case 'object':
            case 'text':
            case 'char':
            case 'varchar':
            case 'string':
            case 'gzip':
                $length = !empty($field['length'])
                    ? $field['length'] : false;

                $fixed  = ((isset($field['fixed']) && $field['fixed']) || $field['type'] == 'char') ? true : false;

                return $fixed ? ($length ? 'CHAR('.$length.')' : 'CHAR('.$this->conn->varchar_max_length.')')
                    : (($length && $length <= $this->conn->varchar_max_length) ? 'VARCHAR('.$length.')' : 'TEXT');
            case 'clob':
                if ( ! empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 8000) {
                        return 'VARCHAR('.$length.')';
                    }
                 }
                 return 'TEXT';
            case 'blob':
                if ( ! empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 8000) {
                        return "VARBINARY($length)";
                    }
                }
                return 'IMAGE';
            case 'integer':
            case 'int':
                return (isset($field['unsigned']) && $field['unsigned']) ? 'BIGINT' : 'INT';
            case 'boolean':
                return 'BIT';
            case 'date':
                return 'CHAR(' . strlen('YYYY-MM-DD') . ')';
            case 'time':
                return 'CHAR(' . strlen('HH:MM:SS') . ')';
            case 'timestamp':
                return 'CHAR(' . strlen('YYYY-MM-DD HH:MM:SS') . ')';
            case 'float':
                return 'FLOAT';
            case 'decimal':
                $length = !empty($field['length']) ? $field['length'] : 18;
                $scale = !empty($field['scale']) ? $field['scale'] : $this->conn->getAttribute(Doctrine_Core::ATTR_DECIMAL_PLACES);
                return 'DECIMAL('.$length.','.$scale.')';
        }
        return $field['type'] . (isset($field['length']) ? '('.$field['length'].')':null);
    }

    /**
     * Maps a native array description of a field to a MDB2 datatype and length
     *
     * @param   array           $field native field description
     * @return  array           containing the various possible types, length, sign, fixed
     */
    public function getPortableDeclaration($field)
    {
        $db_type = preg_replace('/[\d\(\)]/','', strtolower($field['type']) );
        $length  = (isset($field['length']) && $field['length'] > 0) ? $field['length'] : null;

        $type = array();
        // todo: unsigned handling seems to be missing
        $unsigned = $fixed = null;

        if ( ! isset($field['name']))
            $field['name'] = '';

        switch ($db_type) {
            case 'bit':
                $type[0] = 'boolean';
            break;
            case 'tinyint':
            case 'smallint':
            case 'bigint':
            case 'int':
                $type[0] = 'integer';
                if ($length == 1) {
                    $type[] = 'boolean';
                }
            break;
            case 'date': 
                $type[0] = 'date'; 
            break;
            case 'datetime':
            case 'timestamp':
            case 'smalldatetime':
                $type[0] = 'timestamp';
            break;
            case 'float':
            case 'real':
            case 'numeric':
                $type[0] = 'float';
            break;
            case 'decimal':
            case 'money':
            case 'smallmoney':
                $type[0] = 'decimal';
            break;
            case 'text':
            case 'varchar':
            case 'ntext':
            case 'nvarchar':
                $fixed = false;
            case 'char':
            case 'nchar':
                $type[0] = 'string';
                if ($length == '1') {
                    $type[] = 'boolean';
                    if (preg_match('/^[is|has]/', $field['name'])) {
                        $type = array_reverse($type);
                    }
                } elseif (strstr($db_type, 'text')) {
                    $type[] = 'clob';
                }
                if ($fixed !== false) {
                    $fixed = true;
                }
            break;
            case 'image':
            case 'varbinary':
                $type[] = 'blob';
                $length = null;
            break;
            case 'uniqueidentifier':
                $type[] = 'string';
                $length = 36;
            break;
            case 'sql_variant':
            case 'sysname':
            case 'binary':
                $type[] = 'string';
                $length = null;
            break;
            default:
                $type[] = $field['type'];
                $length = isset($field['length']) ? $field['length']:null;
        }

        return array('type'     => $type,
                     'length'   => $length,
                     'unsigned' => $unsigned,
                     'fixed'    => $fixed);
    }

    /**
     * Obtain DBMS specific SQL code portion needed to declare an integer type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string  $name   name the field to be declared.
     * @param string  $field  associative array with the name of the properties
     *                        of the field being declared as array indexes.
     *                        Currently, the types of supported field
     *                        properties are as follows:
     *
     *                       unsigned
     *                        Boolean flag that indicates whether the field
     *                        should be declared as unsigned integer if
     *                        possible.
     *
     *                       default
     *                        Integer value to be used as default for this
     *                        field.
     *
     *                       notnull
     *                        Boolean flag that indicates whether this field is
     *                        constrained to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *                 declare the specified field.
     */
    public function getIntegerDeclaration($name, $field)
    {
        $default = $autoinc = '';
        if ( ! empty($field['autoincrement'])) {
            $autoinc = ' identity';
        } elseif (array_key_exists('default', $field)) {
            if ($field['default'] === '') {
                $field['default'] = empty($field['notnull']) ? null : 0;
            }

            $value = (is_null($field['default'])
                ? 'NULL'
                : $this->conn->quote($field['default']));

            // Name the constraint if a name has been supplied
            if (array_key_exists('defaultConstraintName', $field)) {
                $default .= ' CONSTRAINT ' . $field['defaultConstraintName'];
            }

            $default .= ' DEFAULT ' . $value;
        }


        $notnull = (isset($field['notnull']) && $field['notnull']) ? ' NOT NULL' : ' NULL';
        //$unsigned = (isset($field['unsigned']) && $field['unsigned']) ? ' UNSIGNED' : '';
        // MSSQL does not support the UNSIGNED keyword
        $unsigned = '';
        $comment  = (isset($field['comment']) && $field['comment']) 
            ? " COMMENT " . $this->conn->quote($field['comment'], 'text') : '';

        $name = $this->conn->quoteIdentifier($name, true);

        return $name . ' ' . $this->getNativeDeclaration($field) . $unsigned
            . $default . $notnull . $autoinc . $comment;
    }
}