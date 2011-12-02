<?php
/*
 *  $Id: Mysql.php 7490 2010-03-29 19:53:27Z jwage $
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
 * Doctrine_Connection_Mysql
 *
 * @package     Doctrine
 * @subpackage  Connection
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Lukas Smith <smith@pooteeweet.org> (PEAR MDB2 library)
 * @version     $Revision: 7490 $
 * @link        www.doctrine-project.org
 * @since       1.0
 */
class Doctrine_Connection_Mysql extends Doctrine_Connection_Common
{
    /**
     * @var string $driverName                  the name of this connection driver
     */
    protected $driverName = 'Mysql';

    /**
     * the constructor
     *
     * @param Doctrine_Manager $manager
     * @param PDO|Doctrine_Adapter $adapter     database handler
     */
    public function __construct(Doctrine_Manager $manager, $adapter)
    {
        $this->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_TYPE, 'INNODB');
        $this->supported = array(
                          'sequences'            => 'emulated',
                          'indexes'              => true,
                          'affected_rows'        => true,
                          'transactions'         => true,
                          'savepoints'           => false,
                          'summary_functions'    => true,
                          'order_by_text'        => true,
                          'current_id'           => 'emulated',
                          'limit_queries'        => true,
                          'LOBs'                 => true,
                          'replace'              => true,
                          'sub_selects'          => true,
                          'auto_increment'       => true,
                          'primary_key'          => true,
                          'result_introspection' => true,
                          'prepared_statements'  => 'emulated',
                          'identifier_quoting'   => true,
                          'pattern_escaping'     => true
                          );

        $this->properties['string_quoting'] = array('start' => "'",
                                                    'end' => "'",
                                                    'escape' => '\\',
                                                    'escape_pattern' => '\\');

        $this->properties['identifier_quoting'] = array('start' => '`',
                                                        'end' => '`',
                                                        'escape' => '`');

        $this->properties['sql_comments'] = array(
                                            array('start' => '-- ', 'end' => "\n", 'escape' => false),
                                            array('start' => '#', 'end' => "\n", 'escape' => false),
                                            array('start' => '/*', 'end' => '*/', 'escape' => false),
                                            );

        $this->properties['varchar_max_length'] = 255;

        parent::__construct($manager, $adapter);
    }

    /**
     * Overrides connect Method, to add specific attributes
     * PDO emulate prepares is required to avoid bugs on mysql < 5.1
     * when trying to prepare DROP DATABASE or CREATE DATABASE statements
     *
     * @see Doctrine_Connection :: connect();
     * @return boolean connected
     */
     public function connect()
     {
         $connected = parent::connect();
         $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

         return $connected;
     }
    
    
    /**
     * returns the name of the connected database
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->fetchOne('SELECT DATABASE()');
    }

    /**
     * Set the charset on the current connection
     *
     * @param string    charset
     */
    public function setCharset($charset)
    {
        $query = 'SET NAMES ' . $this->quote($charset);
        $this->exec($query);
        parent::setCharset($charset);
    }

    /**
     * Execute a SQL REPLACE query. A REPLACE query is identical to a INSERT
     * query, except that if there is already a row in the table with the same
     * key field values, the REPLACE query just updates its values instead of
     * inserting a new row.
     *
     * The REPLACE type of query does not make part of the SQL standards. Since
     * practically only MySQL implements it natively, this type of query is
     * emulated through this method for other DBMS using standard types of
     * queries inside a transaction to assure the atomicity of the operation.
     *
     * @access public
     *
     * @param string $table name of the table on which the REPLACE query will
     *  be executed.
     * @param array $fields associative array that describes the fields and the
     *  values that will be inserted or updated in the specified table. The
     *  indexes of the array are the names of all the fields of the table. The
     *  values of the array are also associative arrays that describe the
     *  values and other properties of the table fields.
     *
     *  Here follows a list of field properties that need to be specified:
     *
     *    value:
     *          Value to be assigned to the specified field. This value may be
     *          of specified in database independent type format as this
     *          function can perform the necessary datatype conversions.
     *
     *    Default:
     *          this property is required unless the Null property
     *          is set to 1.
     *
     *    type
     *          Name of the type of the field. Currently, all types Metabase
     *          are supported except for clob and blob.
     *
     *    Default: no type conversion
     *
     *    null
     *          Boolean property that indicates that the value for this field
     *          should be set to null.
     *
     *          The default value for fields missing in INSERT queries may be
     *          specified the definition of a table. Often, the default value
     *          is already null, but since the REPLACE may be emulated using
     *          an UPDATE query, make sure that all fields of the table are
     *          listed in this function argument array.
     *
     *    Default: 0
     *
     *    key
     *          Boolean property that indicates that this field should be
     *          handled as a primary key or at least as part of the compound
     *          unique index of the table that will determine the row that will
     *          updated if it exists or inserted a new row otherwise.
     *
     *          This function will fail if no key field is specified or if the
     *          value of a key field is set to null because fields that are
     *          part of unique index they may not be null.
     *
     *    Default: 0
     *
     * @return integer      the number of affected rows
     */
    public function replace(Doctrine_Table $table, array $fields, array $keys)
    {
        if (empty($keys)) {
            throw new Doctrine_Connection_Exception('Not specified which fields are keys');
        }

        $columns = array();
        $values = array();
        $params = array();
        foreach ($fields as $fieldName => $value) {
            $columns[] = $table->getColumnName($fieldName);
            $values[] = '?';
            $params[] = $value;
        }

        $query = 'REPLACE INTO ' . $this->quoteIdentifier($table->getTableName()) . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';

        return $this->exec($query, $params);
    }
}