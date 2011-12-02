<?php
/*
 *  $Id: Pgsql.php 7641 2010-06-08 14:50:30Z jwage $
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
 * @author      Paul Cooper <pgc@ucecom.com>
 * @author      Lukas Smith <smith@pooteeweet.org> (PEAR MDB2 library)
 * @version     $Revision: 7641 $
 * @link        www.doctrine-project.org
 * @since       1.0
 */
class Doctrine_DataDict_Pgsql extends Doctrine_DataDict
{
    /**
     * @param array $reservedKeyWords     an array of reserved keywords by pgsql
     */
    protected static $reservedKeyWords = array(
                                        'abort',
                                        'absolute',
                                        'access',
                                        'action',
                                        'add',
                                        'after',
                                        'aggregate',
                                        'all',
                                        'alter',
                                        'analyse',
                                        'analyze',
                                        'and',
                                        'any',
                                        'as',
                                        'asc',
                                        'assertion',
                                        'assignment',
                                        'at',
                                        'authorization',
                                        'backward',
                                        'before',
                                        'begin',
                                        'between',
                                        'bigint',
                                        'binary',
                                        'bit',
                                        'boolean',
                                        'both',
                                        'by',
                                        'cache',
                                        'called',
                                        'cascade',
                                        'case',
                                        'cast',
                                        'chain',
                                        'char',
                                        'character',
                                        'characteristics',
                                        'check',
                                        'checkpoint',
                                        'class',
                                        'close',
                                        'cluster',
                                        'coalesce',
                                        'collate',
                                        'column',
                                        'comment',
                                        'commit',
                                        'committed',
                                        'constraint',
                                        'constraints',
                                        'conversion',
                                        'convert',
                                        'copy',
                                        'create',
                                        'createdb',
                                        'createuser',
                                        'cross',
                                        'current_date',
                                        'current_time',
                                        'current_timestamp',
                                        'current_user',
                                        'cursor',
                                        'cycle',
                                        'database',
                                        'day',
                                        'deallocate',
                                        'dec',
                                        'decimal',
                                        'declare',
                                        'default',
                                        'deferrable',
                                        'deferred',
                                        'definer',
                                        'delete',
                                        'delimiter',
                                        'delimiters',
                                        'desc',
                                        'distinct',
                                        'do',
                                        'domain',
                                        'double',
                                        'drop',
                                        'each',
                                        'else',
                                        'encoding',
                                        'encrypted',
                                        'end',
                                        'escape',
                                        'except',
                                        'exclusive',
                                        'execute',
                                        'exists',
                                        'explain',
                                        'external',
                                        'extract',
                                        'false',
                                        'fetch',
                                        'float',
                                        'for',
                                        'force',
                                        'foreign',
                                        'forward',
                                        'freeze',
                                        'from',
                                        'full',
                                        'function',
                                        'get',
                                        'global',
                                        'grant',
                                        'group',
                                        'handler',
                                        'having',
                                        'hour',
                                        'ilike',
                                        'immediate',
                                        'immutable',
                                        'implicit',
                                        'in',
                                        'increment',
                                        'index',
                                        'inherits',
                                        'initially',
                                        'inner',
                                        'inout',
                                        'input',
                                        'insensitive',
                                        'insert',
                                        'instead',
                                        'int',
                                        'integer',
                                        'intersect',
                                        'interval',
                                        'into',
                                        'invoker',
                                        'is',
                                        'isnull',
                                        'isolation',
                                        'join',
                                        'key',
                                        'lancompiler',
                                        'language',
                                        'leading',
                                        'left',
                                        'level',
                                        'like',
                                        'limit',
                                        'listen',
                                        'load',
                                        'local',
                                        'localtime',
                                        'localtimestamp',
                                        'location',
                                        'lock',
                                        'match',
                                        'maxvalue',
                                        'minute',
                                        'minvalue',
                                        'mode',
                                        'month',
                                        'move',
                                        'names',
                                        'national',
                                        'natural',
                                        'nchar',
                                        'new',
                                        'next',
                                        'no',
                                        'nocreatedb',
                                        'nocreateuser',
                                        'none',
                                        'not',
                                        'nothing',
                                        'notify',
                                        'notnull',
                                        'null',
                                        'nullif',
                                        'numeric',
                                        'of',
                                        'off',
                                        'offset',
                                        'oids',
                                        'old',
                                        'on',
                                        'only',
                                        'operator',
                                        'option',
                                        'or',
                                        'order',
                                        'out',
                                        'outer',
                                        'overlaps',
                                        'overlay',
                                        'owner',
                                        'partial',
                                        'password',
                                        'path',
                                        'pendant',
                                        'placing',
                                        'position',
                                        'precision',
                                        'prepare',
                                        'primary',
                                        'prior',
                                        'privileges',
                                        'procedural',
                                        'procedure',
                                        'read',
                                        'real',
                                        'recheck',
                                        'references',
                                        'reindex',
                                        'relative',
                                        'rename',
                                        'replace',
                                        'reset',
                                        'restrict',
                                        'returns',
                                        'revoke',
                                        'right',
                                        'rollback',
                                        'row',
                                        'rule',
                                        'schema',
                                        'scroll',
                                        'second',
                                        'security',
                                        'select',
                                        'sequence',
                                        'serializable',
                                        'session',
                                        'session_user',
                                        'set',
                                        'setof',
                                        'share',
                                        'show',
                                        'similar',
                                        'simple',
                                        'smallint',
                                        'some',
                                        'stable',
                                        'start',
                                        'statement',
                                        'statistics',
                                        'stdin',
                                        'stdout',
                                        'storage',
                                        'strict',
                                        'substring',
                                        'sysid',
                                        'table',
                                        'temp',
                                        'template',
                                        'temporary',
                                        'then',
                                        'time',
                                        'timestamp',
                                        'to',
                                        'toast',
                                        'trailing',
                                        'transaction',
                                        'treat',
                                        'trigger',
                                        'trim',
                                        'true',
                                        'truncate',
                                        'trusted',
                                        'type',
                                        'unencrypted',
                                        'union',
                                        'unique',
                                        'unknown',
                                        'unlisten',
                                        'until',
                                        'update',
                                        'usage',
                                        'user',
                                        'using',
                                        'vacuum',
                                        'valid',
                                        'validator',
                                        'values',
                                        'varchar',
                                        'varying',
                                        'verbose',
                                        'version',
                                        'view',
                                        'volatile',
                                        'when',
                                        'where',
                                        'with',
                                        'without',
                                        'work',
                                        'write',
                                        'year',
                                        'zone'
                                        );

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
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     */
    public function getNativeDeclaration(array $field)
    {
        if ( ! isset($field['type'])) {
            throw new Doctrine_DataDict_Exception('Missing column type.');
        }

        // Postgres enum type by name containing enum
        if (strpos($field['type'], 'enum') !== false){
            $field['type'] = 'enum';            
        }

        switch ($field['type']) {
            case 'enum':
                $field['length'] = isset($field['length']) && $field['length'] ? $field['length']:255;
            case 'char':
            case 'string':
            case 'array':
            case 'object':
            case 'varchar':
            case 'gzip':
                // TODO: what is the maximum VARCHAR length in pgsql ?
                $length = (isset($field['length']) && $field['length'] && $field['length'] < 10000) ? $field['length'] : null;

                $fixed  = ((isset($field['fixed']) && $field['fixed']) || $field['type'] == 'char') ? true : false;

                return $fixed ? ($length ? 'CHAR(' . $length . ')' : 'CHAR('.$this->conn->varchar_max_length.')')
                    : ($length ? 'VARCHAR(' .$length . ')' : 'TEXT');

            case 'clob':
                return 'TEXT';
            case 'blob':
                return 'BYTEA';
            case 'integer':
            case 'int':
                if ( ! empty($field['autoincrement'])) {
                    if ( ! empty($field['length'])) {
                        $length = $field['length'];
                        if ($length > 4) {
                            return 'BIGSERIAL';
                        }
                    }
                    return 'SERIAL';
                }
                if ( ! empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 2) {
                        return 'SMALLINT';
                    } elseif ($length == 3 || $length == 4) {
                        return 'INT';
                    } elseif ($length > 4) {
                        return 'BIGINT';
                    }
                }
                return 'INT';
			case 'inet':
				return 'INET';
            case 'bit':
            case 'varbit':
                return 'VARBIT';		
            case 'boolean':
                return 'BOOLEAN';
            case 'date':
                return 'DATE';
            case 'time':
                return 'TIME';
            case 'timestamp':
                return 'TIMESTAMP';
            case 'float':
            case 'double':
                return 'FLOAT';
            case 'decimal':
                $length = !empty($field['length']) ? $field['length'] : 18;
                $scale = !empty($field['scale']) ? $field['scale'] : $this->conn->getAttribute(Doctrine_Core::ATTR_DECIMAL_PLACES);
                return 'NUMERIC('.$length.','.$scale.')';
        }
        return $field['type'] . (isset($field['length']) ? '('.$field['length'].')':null);
    }

    /**
     * Maps a native array description of a field to a portable Doctrine datatype and length
     *
     * @param array  $field native field description
     *
     * @return array containing the various possible types, length, sign, fixed
     */
    public function getPortableDeclaration(array $field)
    {
        $length = (isset($field['length'])) ? $field['length'] : null;
        if ($length == '-1' && isset($field['atttypmod'])) {
            $length = $field['atttypmod'] - 4;
        }
        if ((int)$length <= 0) {
            $length = null;
        }
        $type = array();
        $unsigned = $fixed = null;

        if ( ! isset($field['name'])) {
            $field['name'] = '';
        }

        $dbType = strtolower($field['type']);

        // Default from field for enum support
        $default = isset($field['default']) ? $field['default'] : null;
        $enumName = null;
        if (strpos($dbType, 'enum') !== false){
            $enumName = $dbType;
            $dbType = 'enum';
        }

        switch ($dbType) {
    	    case 'inet':
                    $type[] = 'inet';
    		break;
    	    case 'bit':
    	    case 'varbit':
                    $type[] = 'bit';
    		break;
            case 'smallint':
            case 'int2':
                $type[] = 'integer';
                $unsigned = false;
                $length = 2;
                if ($length == '2') {
                    $type[] = 'boolean';
                    if (preg_match('/^(is|has)/', $field['name'])) {
                        $type = array_reverse($type);
                    }
                }
                break;
            case 'int':
            case 'int4':
            case 'integer':
            case 'serial':
            case 'serial4':
                $type[] = 'integer';
                $unsigned = false;
                $length = 4;
                break;
            case 'bigint':
            case 'int8':
            case 'bigserial':
            case 'serial8':
                $type[] = 'integer';
                $unsigned = false;
                $length = 8;
                break;
            case 'bool':
            case 'boolean':
                $type[] = 'boolean';
                $length = 1;
                break;
            case 'text':
            case 'varchar':
            case 'interval':
            case '_varchar':
                $fixed = false;
            case 'tsvector':
            case 'unknown':
            case 'char':
            case 'character':
            case 'bpchar':
                $type[] = 'string';
                if ($length == '1') {
                    $type[] = 'boolean';
                    if (preg_match('/^(is|has)/', $field['name'])) {
                        $type = array_reverse($type);
                    }
                } elseif (strstr($dbType, 'text')) {
                    $type[] = 'clob';
                }
                if ($fixed !== false) {
                    $fixed = true;
                }
                break;
            case 'enum':
                $type[] = 'enum';
                $length = $length ? $length :255;
                if($default) {
                    $default = preg_replace('/\'(\w+)\'.*/', '${1}', $default);
                }
                break;
            case 'date':
                $type[] = 'date';
                $length = null;
                break;
            case 'datetime':
            case 'timestamp':
            case 'timetz':
            case 'timestamptz':
                $type[] = 'timestamp';
                $length = null;
                break;
            case 'time':
                $type[] = 'time';
                $length = null;
                break;
            case 'float':
            case 'float4':
            case 'float8':
            case 'double':
            case 'double precision':
            case 'real':
                $type[] = 'float';
                break;
            case 'decimal':
            case 'money':
            case 'numeric':
                $type[] = 'decimal';
                break;
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
            case 'blob':
            case 'bytea':
            case 'geometry':
            case 'geometrycollection':
            case 'point':
            case 'multipoint':
            case 'linestring':
            case 'multilinestring':
            case 'polygon':
            case 'multipolygon':
                $type[] = 'blob';
                $length = null;
                break;
            case 'oid':
                $type[] = 'blob';
                $type[] = 'clob';
                $length = null;
                break;
            case 'year':
                $type[] = 'integer';
                $type[] = 'date';
                $length = null;
                break;
            default:
                $type[] = $field['type'];
                $length = isset($field['length']) ? $field['length']:null;
        }

        $ret = array('type'     => $type,
                     'length'   => $length,
                     'unsigned' => $unsigned,
                     'fixed'    => $fixed);

        // If this is postgresql enum type we will have non-null values here
        if ($default !== null) {
            $ret['default'] = $default;
        }
        if ($enumName !== null) {
            $ret['enumName'] = $enumName;
        }
        return $ret;
    }

    /**
     * Obtain DBMS specific SQL code portion needed to declare an integer type
     * field to be used in statements like CREATE TABLE.
     *
     * @param string $name name the field to be declared.
     * @param array $field associative array with the name of the properties
     *       of the field being declared as array indexes. Currently, the types
     *       of supported field properties are as follows:
     *
     *       unsigned
     *           Boolean flag that indicates whether the field should be
     *           declared as unsigned integer if possible.
     *
     *       default
     *           Integer value to be used as default for this field.
     *
     *       notnull
     *           Boolean flag that indicates whether this field is constrained
     *           to not be set to null.
     * @return string DBMS specific SQL code portion that should be used to
     *       declare the specified field.
     */
    public function getIntegerDeclaration($name, $field)
    {
        /**
        if ( ! empty($field['unsigned'])) {
            $this->conn->warnings[] = "unsigned integer field \"$name\" is being declared as signed integer";
        }
        */

        if ( ! empty($field['autoincrement'])) {
            $name = $this->conn->quoteIdentifier($name, true);
            return $name . ' ' . $this->getNativeDeclaration($field);
        }

        $default = '';
        if (array_key_exists('default', $field)) {
            if ($field['default'] === '') {
                $field['default'] = empty($field['notnull']) ? null : 0;
            }

            $default = ' DEFAULT ' . (is_null($field['default'])
                ? 'NULL'
                : $this->conn->quote($field['default'], $field['type']));
        }
        /**
        TODO: is this needed ?
        elseif (empty($field['notnull'])) {
            $default = ' DEFAULT NULL';
        }
        */

        $notnull = empty($field['notnull']) ? '' : ' NOT NULL';
        $name = $this->conn->quoteIdentifier($name, true);
        return $name . ' ' . $this->getNativeDeclaration($field) . $default . $notnull;
    }

    /**
     * parseBoolean
     * parses a literal boolean value and returns
     * proper sql equivalent
     *
     * @param string $value     boolean value to be parsed
     * @return string           parsed boolean value
     */
    public function parseBoolean($value)
    {
        return $value;
    }
}
