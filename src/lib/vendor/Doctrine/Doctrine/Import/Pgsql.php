<?php
/*
 *  $Id: Pgsql.php 7689 2010-08-25 23:50:42Z jwage $
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
 * @subpackage  Import
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Paul Cooper <pgc@ucecom.com>
 * @author      Lukas Smith <smith@pooteeweet.org> (PEAR MDB2 library)
 * @version     $Revision: 7689 $
 * @link        www.doctrine-project.org
 * @since       1.0
 */
class Doctrine_Import_Pgsql extends Doctrine_Import
{

    protected $sql = array(
                        'listDatabases' => 'SELECT datname FROM pg_database',
                        'listFunctions' => "SELECT
                                                proname
                                            FROM
                                                pg_proc pr,
                                                pg_type tp
                                            WHERE
                                                tp.oid = pr.prorettype
                                                AND pr.proisagg = FALSE
                                                AND tp.typname <> 'trigger'
                                                AND pr.pronamespace IN
                                                    (SELECT oid FROM pg_namespace
                                                     WHERE nspname NOT LIKE 'pg_%' AND nspname != 'information_schema'",
                        'listSequences' => "SELECT
                                                regexp_replace(relname, '_seq$', '')
                                            FROM
                                                pg_class
                                            WHERE relkind = 'S' AND relnamespace IN
                                                (SELECT oid FROM pg_namespace
                                                 WHERE nspname NOT LIKE 'pg_%' AND nspname != 'information_schema')",
                        'listTables'    => "SELECT
                                                c.relname AS table_name
                                            FROM pg_class c, pg_user u
                                            WHERE c.relowner = u.usesysid
                                                AND c.relkind = 'r'
                                                AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname AND schemaname <> 'information_schema')
                                                AND c.relname !~ '^(pg_|sql_)'
                                            UNION
                                            SELECT c.relname AS table_name
                                            FROM pg_class c
                                            WHERE c.relkind = 'r'
                                                AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname)
                                                AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner)
                                                AND c.relname !~ '^pg_'",
                        'listViews'     => 'SELECT viewname FROM pg_views',
                        'listUsers'     => 'SELECT usename FROM pg_user',
                        'listTableConstraints' => "SELECT
                                                        relname
                                                   FROM
                                                        pg_class
                                                   WHERE oid IN (
                                                        SELECT indexrelid
                                                        FROM pg_index, pg_class
                                                        WHERE pg_class.relname = %s
                                                            AND pg_class.oid = pg_index.indrelid
                                                            AND (indisunique = 't' OR indisprimary = 't')
                                                        )",
                        'listTableIndexes'     => "SELECT
                                                        relname
                                                   FROM
                                                        pg_class
                                                   WHERE oid IN (
                                                        SELECT indexrelid
                                                        FROM pg_index, pg_class
                                                        WHERE pg_class.relname = %s
                                                            AND pg_class.oid=pg_index.indrelid
                                                            AND indisunique != 't'
                                                            AND indisprimary != 't'
                                                        )",
                        'listTableColumns'     => "SELECT
                                                     ordinal_position as attnum,
                                                     column_name as field,
                                                     udt_name as type,
                                                     data_type as complete_type,
                                                     is_nullable as isnotnull,
                                                     column_default as default,
                                                     (
                                                       SELECT 't'
                                                         FROM pg_index, pg_attribute a, pg_class c, pg_type t
                                                         WHERE c.relname = table_name AND a.attname = column_name
                                                         AND a.attnum > 0 AND a.attrelid = c.oid AND a.atttypid = t.oid
                                                         AND c.oid = pg_index.indrelid AND a.attnum = ANY (pg_index.indkey)
                                                         AND pg_index.indisprimary = 't'
                                                     ) as pri,
                                                     character_maximum_length as length
                                                   FROM information_schema.COLUMNS
                                                   WHERE table_name = %s
                                                   ORDER BY ordinal_position",
                        'listTableRelations'   => "SELECT pg_catalog.pg_get_constraintdef(oid, true) as condef
                                                          FROM pg_catalog.pg_constraint r
                                                          WHERE r.conrelid =
                                                          (
                                                              SELECT c.oid
                                                              FROM pg_catalog.pg_class c
                                                              LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
                                                              WHERE c.relname ~ ? AND pg_catalog.pg_table_is_visible(c.oid)
                                                          )
                                                          AND r.contype = 'f'"
                        );

    /**
     * lists all database triggers
     *
     * @param string|null $database
     * @return array
     */
    public function listTriggers($database = null)
    {

    }

    /**
     * lists table constraints
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableConstraints($table)
    {
        $table = $this->conn->quote($table);
        $query = sprintf($this->sql['listTableConstraints'], $table);

        return $this->conn->fetchColumn($query);
    }

    /**
     * lists table constraints
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableColumns($table)
    {
        $table = $this->conn->quote($table);
        $query = sprintf($this->sql['listTableColumns'], $table);
        $result = $this->conn->fetchAssoc($query);

        $columns     = array();
        foreach ($result as $key => $val) {
            $val = array_change_key_case($val, CASE_LOWER);

            if ($val['type'] == 'character varying') {
                // get length from varchar definition
                $length = preg_replace('~.*\(([0-9]*)\).*~', '$1', $val['complete_type']);
                $val['length'] = $length;
            } else if (strpos($val['complete_type'], 'character varying') !== false) {
                // get length from varchar definition
                $length = preg_replace('~.*\(([0-9]*)\).*~', '$1', $val['complete_type']);
                $val['length'] = $length;
            }
            
            $decl = $this->conn->dataDict->getPortableDeclaration($val);

            $description = array(
                'name'      => $val['field'],
                'ntype'     => $val['type'],
                'type'      => $decl['type'][0],
                'alltypes'  => $decl['type'],
                'length'    => $decl['length'],
                'fixed'     => (bool) $decl['fixed'],
                'unsigned'  => (bool) $decl['unsigned'],
                'notnull'   => ($val['isnotnull'] == 'NO'),
                'default'   => $val['default'],
                'primary'   => ($val['pri'] == 't'),
            );

            // If postgres enum type            
            if ($val['type'] == 'e'){
                $description['default'] = isset($decl['default']) ? $decl['default'] : null;
                $t_result = $this->conn->fetchAssoc(sprintf('select enum_range(null::%s) as range ', $decl['enum_name']));                
                if (isset($t_result[0])){
                    $range =  $t_result[0]['range'];
                    $range = str_replace('{','',$range);
                    $range = str_replace('}','',$range);
                    $range = explode(',',$range);
                    $description['values'] = $range;
                }
            }

            $matches = array(); 

            if (preg_match("/^nextval\('(.*)'(::.*)?\)$/", $description['default'], $matches)) { 
                $description['sequence'] = $this->conn->formatter->fixSequenceName($matches[1]); 
                $description['default'] = null; 
            } else if (preg_match("/^'(.*)'::character varying$/", $description['default'], $matches)) {
                $description['default'] = $matches[1];
            } else if (preg_match("/^(.*)::character varying$/", $description['default'], $matches)) {
                $description['default'] = $matches[1];
            } else if ($description['type'] == 'boolean') {
                if ($description['default'] === 'true') {
                   $description['default'] = true;
                } else if ($description['default'] === 'false') {
                   $description['default'] = false;
                }
            }

            $columns[$val['field']] = $description;
        }
        
        return $columns;
    }

    /**
     * list all indexes in a table
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableIndexes($table)
    {
        $table = $this->conn->quote($table);
        $query = sprintf($this->sql['listTableIndexes'], $table);

        return $this->conn->fetchColumn($query);
    }

    /**
     * lists tables
     *
     * @param string|null $database
     * @return array
     */
    public function listTables($database = null)
    {
        return $this->conn->fetchColumn($this->sql['listTables']);
    }

    /**
     * lists table triggers
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableTriggers($table)
    {
        $query = 'SELECT trg.tgname AS trigger_name
                    FROM pg_trigger trg,
                         pg_class tbl
                   WHERE trg.tgrelid = tbl.oid';
        if ($table !== null) {
            $table = $this->conn->quote(strtoupper($table), 'string');
            $query .= " AND tbl.relname = $table";
        }
        return $this->conn->fetchColumn($query);
    }

    /**
     * list the views in the database that reference a given table
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableViews($table)
    {
        return $this->conn->fetchColumn($table);
    }

    public function listTableRelations($table)
    {
        $sql = $this->sql['listTableRelations'];
        $param = array('^(' . $table . ')$');

        $relations = array();

        $results = $this->conn->fetchAssoc($sql, $param);
        foreach ($results as $result) {
            preg_match('/FOREIGN KEY \((.+)\) REFERENCES (.+)\((.+)\)/', $result['condef'], $values);
            if ((strpos($values[1], ',') === false) && (strpos($values[3], ',') === false)) {
                $tableName = trim($values[2], '"');
                $relations[] = array('table'   => $tableName,
                                     'local'   => $values[1],
                                     'foreign' => $values[3]);
            }
        }

        return $relations;
    }
}