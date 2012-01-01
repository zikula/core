<?php
/*
 *  $Id: Oracle.php 7490 2010-03-29 19:53:27Z jwage $
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
 * Doctrine_Export_Oracle
 *
 * @package     Doctrine
 * @subpackage  Export
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Lukas Smith <smith@pooteeweet.org> (PEAR MDB2 library)
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 7490 $
 */
class Doctrine_Export_Oracle extends Doctrine_Export
{
    /**
     * create a new database
     *
     * @param object $db database object that is extended by this class
     * @param string $name name of the database that should be created
     * @return boolean      success of operation
     */
    public function createDatabase($name)
    {
        if ($this->conn->getAttribute(Doctrine_Core::ATTR_EMULATE_DATABASE)) {
            $username   = $name;
            $password   = $this->conn->dsn['password'] ? $this->conn->dsn['password'] : $name;

            $tablespace = $this->conn->options['default_tablespace']
                        ? ' DEFAULT TABLESPACE '.$this->conn->options['default_tablespace'] : '';

            $query  = 'CREATE USER ' . $username . ' IDENTIFIED BY ' . $password . $tablespace;
            $result = $this->conn->exec($query);

            try {
                $query = 'GRANT CREATE SESSION, CREATE TABLE, UNLIMITED TABLESPACE, CREATE SEQUENCE, CREATE TRIGGER TO ' . $username;
                $result = $this->conn->exec($query);
            } catch (Exception $e) {
                $this->dropDatabase($username);
            }
        }
        return true;
    }

    /**
     * drop an existing database
     *
     * @param object $this->conn database object that is extended by this class
     * @param string $name name of the database that should be dropped
     * @return boolean      success of operation
     * @access public
     */
    public function dropDatabase($name)
    {
        $sql = <<<SQL
BEGIN
  -- user_tables contains also materialized views
  FOR I IN (SELECT table_name FROM user_tables WHERE table_name NOT IN (SELECT mview_name FROM user_mviews))
  LOOP 
    EXECUTE IMMEDIATE 'DROP TABLE "'||I.table_name||'" CASCADE CONSTRAINTS';
  END LOOP;
  
  FOR I IN (SELECT SEQUENCE_NAME FROM USER_SEQUENCES)
  LOOP
    EXECUTE IMMEDIATE 'DROP SEQUENCE "'||I.SEQUENCE_NAME||'"';
  END LOOP;
END;

SQL;

        $this->conn->exec($sql);

        if ($this->conn->getAttribute(Doctrine_Core::ATTR_EMULATE_DATABASE)) {
            $username = $name;
            $this->conn->exec('DROP USER ' . $username . ' CASCADE');
        }
    }

    /**
     * add an autoincrement sequence + trigger
     *
     * @param string $name  name of the PK field
     * @param string $table name of the table
     * @param string $start start value for the sequence
     * @return string        Sql code
     * @access private
     */
    public function _makeAutoincrement($name, $table, $start = 1)
    {
        $sql   = array();

        if ( ! $this->conn->getAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER)) {
        	$table = strtoupper($table);
        }
        $indexName  = $table . '_AI_PK';
        $definition = array(
            'primary' => true,
            'fields' => array($name => true),
        );
		
        $sql[] = 'DECLARE
  constraints_Count NUMBER;
BEGIN
  SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count FROM USER_CONSTRAINTS WHERE TABLE_NAME = \''.$table.'\' AND CONSTRAINT_TYPE = \'P\';
  IF constraints_Count = 0 THEN
    EXECUTE IMMEDIATE \''.$this->createConstraintSql($table, $indexName, $definition).'\';
  END IF;
END;';   
		
        if (is_null($start)) {
            $query = 'SELECT MAX(' . $this->conn->quoteIdentifier($name, true) . ') FROM ' . $this->conn->quoteIdentifier($table, true);
            $start = $this->conn->fetchOne($query);

            ++$start;
        }

        $sql[] = $this->createSequenceSql($table, $start);

        $sequenceName = $this->conn->formatter->getSequenceName($table);
        $triggerName  = $this->conn->quoteIdentifier($table . '_AI_PK', true);
        $table = $this->conn->quoteIdentifier($table, true);
        $name  = $this->conn->quoteIdentifier($name, true);
        $sql[] = 'CREATE TRIGGER ' . $triggerName . '
   BEFORE INSERT
   ON ' . $table . '
   FOR EACH ROW
DECLARE
   last_Sequence NUMBER;
   last_InsertID NUMBER;
BEGIN
   IF (:NEW.' . $name . ' IS NULL OR :NEW.'.$name.' = 0) THEN
      SELECT ' . $this->conn->quoteIdentifier($sequenceName) . '.NEXTVAL INTO :NEW.' . $name . ' FROM DUAL;
   ELSE
      SELECT NVL(Last_Number, 0) INTO last_Sequence
        FROM User_Sequences
       WHERE UPPER(Sequence_Name) = UPPER(\'' . $sequenceName . '\');
      SELECT :NEW.' . $name . ' INTO last_InsertID FROM DUAL;
      WHILE (last_InsertID > last_Sequence) LOOP
         SELECT ' . $this->conn->quoteIdentifier($sequenceName) . '.NEXTVAL INTO last_Sequence FROM DUAL;
      END LOOP;
   END IF;
END;';
        return $sql;
    }

    /**
     * drop an existing autoincrement sequence + trigger
     *
     * @param string $table name of the table
     * @return void
     */
    public function dropAutoincrement($table)
    {
        $table = strtoupper($table);
        $triggerName = $table . '_AI_PK';
        $trigger_name_quoted = $this->conn->quote($triggerName);
        $query = 'SELECT trigger_name FROM user_triggers';
        $query.= ' WHERE trigger_name='.$trigger_name_quoted.' OR trigger_name='.strtoupper($trigger_name_quoted);
        $trigger = $this->conn->fetchOne($query);

        if ($trigger) {
            $trigger_name  = $this->conn->quoteIdentifier($table . '_AI_PK', true);
            $trigger_sql = 'DROP TRIGGER ' . $trigger_name;

            // if throws exception, trigger for autoincrement PK could not be dropped
            $this->conn->exec($trigger_sql);

            // if throws exception, sequence for autoincrement PK could not be dropped
            $this->dropSequence($table);

            $indexName = $table . '_AI_PK';

            // if throws exception, primary key for autoincrement PK could not be dropped
            $this->dropConstraint($table, $indexName);
        }
    }
   /**
     * A method to return the required SQL string that fits between CREATE ... TABLE
     * to create the table as a temporary table.
     *
     * @return string The string required to be placed between "CREATE" and "TABLE"
     *                to generate a temporary table, if possible.
     */
    public function getTemporaryTableQuery()
    {
        return 'GLOBAL TEMPORARY';
    }

    /**
     * getAdvancedForeignKeyOptions
     * Return the FOREIGN KEY query section dealing with non-standard options
     * as MATCH, INITIALLY DEFERRED, ON UPDATE, ...
     *
     * @param array $definition         foreign key definition
     * @return string
     * @access protected
     */
    public function getAdvancedForeignKeyOptions(array $definition)
    {
        $query = '';
        if (isset($definition['onDelete']) && strtoupper(trim($definition['onDelete'])) != 'NO ACTION') {
            $query .= ' ON DELETE ' . $definition['onDelete'];
        }
        if (isset($definition['deferrable'])) {
            $query .= ' DEFERRABLE';
        } else {
            $query .= ' NOT DEFERRABLE';
        }
        if (isset($definition['feferred'])) {
            $query .= ' INITIALLY DEFERRED';
        } else {
            $query .= ' INITIALLY IMMEDIATE';
        }
        return $query;
    }

    /**
     * create a new table
     *
     * @param string $name     Name of the database that should be created
     * @param array $fields Associative array that contains the definition of each field of the new table
     *                        The indexes of the array entries are the names of the fields of the table an
     *                        the array entry values are associative arrays like those that are meant to be
     *                         passed with the field definitions to get[Type]Declaration() functions.
     *
     *                        Example
     *                        array(
     *
     *                            'id' => array(
     *                                'type' => 'integer',
     *                                'unsigned' => 1
     *                                'notnull' => 1
     *                                'default' => 0
     *                            ),
     *                            'name' => array(
     *                                'type' => 'text',
     *                                'length' => 12
     *                            ),
     *                            'password' => array(
     *                                'type' => 'text',
     *                                'length' => 12
     *                            )
     *                        );
     * @param array $options  An associative array of table options:
     *
     * @return void
     */
    public function createTable($name, array $fields, array $options = array())
    {
        $this->conn->beginTransaction();

        foreach ($this->createTableSql($name, $fields, $options) as $sql) {
            $this->conn->exec($sql);
        }

        $this->conn->commit();
    }

    /**
     * create a new table
     *
     * @param string $name     Name of the database that should be created
     * @param array $fields Associative array that contains the definition of each field of the new table
     *                        The indexes of the array entries are the names of the fields of the table an
     *                        the array entry values are associative arrays like those that are meant to be
     *                         passed with the field definitions to get[Type]Declaration() functions.
     *
     *                        Example
     *                        array(
     *
     *                            'id' => array(
     *                                'type' => 'integer',
     *                                'unsigned' => 1
     *                                'notnull' => 1
     *                                'default' => 0
     *                            ),
     *                            'name' => array(
     *                                'type' => 'text',
     *                                'length' => 12
     *                            ),
     *                            'password' => array(
     *                                'type' => 'text',
     *                                'length' => 12
     *                            )
     *                        );
     * @param array $options  An associative array of table options:
     *
     * @return void
     */
    public function createTableSql($name, array $fields, array $options = array())
    {
        $sql = parent::createTableSql($name, $fields, $options);

        if (isset($options['comment']) && ! empty($options['comment'])) {
     	    $sql[] = $this->_createTableCommentSql($name, $options['comment']);
     	}

        foreach ($fields as $fieldName => $field) {
            if (isset($field['sequence'])) {
              $sql[] = $this->createSequenceSql($field['sequence'], 1);
            }

            if (isset($field['autoincrement']) && $field['autoincrement'] ||
               (isset($field['autoinc']) && $fields['autoinc'])) {           
                $sql = array_merge($sql, $this->_makeAutoincrement($fieldName, $name));
            }

            if (isset($field['comment']) && ! empty($field['comment'])){
                $sql[] = $this->_createColumnCommentSql($name,$fieldName,$field['comment']); 
            }
        }
        
        if (isset($options['indexes']) && ! empty($options['indexes'])) {
            foreach ($options['indexes'] as $indexName => $definition) {
                // create nonunique indexes, as they are a part od CREATE TABLE DDL
                if ( ! isset($definition['type']) || 
                    (isset($definition['type']) && strtolower($definition['type']) != 'unique')) {
                    $sql[] = $this->createIndexSql($name, $indexName, $definition);
                }
            }
        }
        
        return $sql;
    }

    /**
     * create a comment on a table
     *
     * @param string $table    Name of the table we are commenting
     * @param string $comment  The comment for the table
     *
     * @return string
     */
    public function _createTableCommentSql($table,$comment)
    {
        return 'COMMENT ON TABLE '. $this->conn->quoteIdentifier($table, true). ' IS '.$this->conn->quote($comment, 'text').'';
    }

    /**
     * create a comment on a column
     *
     * @param string $table    Name of the table
     * @param string $column   Name of the column we are commenting
     * @param string $comment  The comment for the table
     *
     * @return string
     */
    public function _createColumnCommentSql($table,$column, $comment)
    {
        return 'COMMENT ON COLUMN '. $this->conn->quoteIdentifier($table, true). '.'. $this->conn->quoteIdentifier($column, true). ' IS '.$this->conn->quote($comment, 'text').'';
    }

    /**
     * drop an existing table
     *
     * @param string $name name of the table that should be dropped
     * @return void
     */
    public function dropTable($name)
    {
        //$this->conn->beginNestedTransaction();
        $result = $this->dropAutoincrement($name);
        $result = parent::dropTable($name);
        //$this->conn->completeNestedTransaction();
        return $result;
    }

    /**
     * alter an existing table
     *
     * @param string $name         name of the table that is intended to be changed.
     * @param array $changes     associative array that contains the details of each type
     *                             of change that is intended to be performed. The types of
     *                             changes that are currently supported are defined as follows:
     *
     *                             name
     *
     *                                New name for the table.
     *
     *                            add
     *
     *                                Associative array with the names of fields to be added as
     *                                 indexes of the array. The value of each entry of the array
     *                                 should be set to another associative array with the properties
     *                                 of the fields to be added. The properties of the fields should
     *                                 be the same as defined by the MDB2 parser.
     *
     *
     *                            remove
     *
     *                                Associative array with the names of fields to be removed as indexes
     *                                 of the array. Currently the values assigned to each entry are ignored.
     *                                 An empty array should be used for future compatibility.
     *
     *                            rename
     *
     *                                Associative array with the names of fields to be renamed as indexes
     *                                 of the array. The value of each entry of the array should be set to
     *                                 another associative array with the entry named name with the new
     *                                 field name and the entry named Declaration that is expected to contain
     *                                 the portion of the field declaration already in DBMS specific SQL code
     *                                 as it is used in the CREATE TABLE statement.
     *
     *                            change
     *
     *                                Associative array with the names of the fields to be changed as indexes
     *                                 of the array. Keep in mind that if it is intended to change either the
     *                                 name of a field and any other properties, the change array entries
     *                                 should have the new names of the fields as array indexes.
     *
     *                                The value of each entry of the array should be set to another associative
     *                                 array with the properties of the fields to that are meant to be changed as
     *                                 array entries. These entries should be assigned to the new values of the
     *                                 respective properties. The properties of the fields should be the same
     *                                 as defined by the MDB2 parser.
     *
     *                            Example
     *                                array(
     *                                    'name' => 'userlist',
     *                                    'add' => array(
     *                                        'quota' => array(
     *                                            'type' => 'integer',
     *                                            'unsigned' => 1
     *                                        )
     *                                    ),
     *                                    'remove' => array(
     *                                        'file_limit' => array(),
     *                                        'time_limit' => array()
     *                                    ),
     *                                    'change' => array(
     *                                        'name' => array(
     *                                            'length' => '20',
     *                                            'definition' => array(
     *                                                'type' => 'text',
     *                                                'length' => 20,
     *                                            ),
     *                                        )
     *                                    ),
     *                                    'rename' => array(
     *                                        'sex' => array(
     *                                            'name' => 'gender',
     *                                            'definition' => array(
     *                                                'type' => 'text',
     *                                                'length' => 1,
     *                                                'default' => 'M',
     *                                            ),
     *                                        )
     *                                    )
     *                                )
     *
     * @param boolean $check     indicates whether the function should just check if the DBMS driver
     *                             can perform the requested table alterations if the value is true or
     *                             actually perform them otherwise.
     * @return void
     */
    public function alterTable($name, array $changes, $check = false)
    {

        foreach ($changes as $changeName => $change) {
            switch ($changeName) {
                case 'add':
                case 'remove':
                case 'change':
                case 'name':
                case 'rename':
                    break;
                default:
                    throw new Doctrine_Export_Exception('change type "' . $changeName . '" not yet supported');
            }
        }

        if ($check) {
            return false;
        }

        $name = $this->conn->quoteIdentifier($name, true);

        if ( ! empty($changes['add']) && is_array($changes['add'])) {
            $fields = array();
            foreach ($changes['add'] as $fieldName => $field) {
                $fields[] = $this->getDeclaration($fieldName, $field); 
            }
            $result = $this->conn->exec('ALTER TABLE ' . $name . ' ADD (' . implode(', ', $fields) . ')');
        }

        if ( ! empty($changes['change']) && is_array($changes['change'])) {
            $fields = array();
            foreach ($changes['change'] as $fieldName => $field) {
                $fields[] = $fieldName. ' ' . $this->getDeclaration('', $field['definition']);
            }
            $result = $this->conn->exec('ALTER TABLE ' . $name . ' MODIFY (' . implode(', ', $fields) . ')');
        }

        if ( ! empty($changes['rename']) && is_array($changes['rename'])) {
            foreach ($changes['rename'] as $fieldName => $field) {
                $query = 'ALTER TABLE ' . $name . ' RENAME COLUMN ' . $this->conn->quoteIdentifier($fieldName, true)
                       . ' TO ' . $this->conn->quoteIdentifier($field['name']);

                $result = $this->conn->exec($query);
            }
        }

        if ( ! empty($changes['remove']) && is_array($changes['remove'])) {
            $fields = array();
            foreach ($changes['remove'] as $fieldName => $field) {
                $fields[] = $this->conn->quoteIdentifier($fieldName, true);
            }
            $result = $this->conn->exec('ALTER TABLE ' . $name . ' DROP COLUMN ' . implode(', ', $fields));
        }

        if ( ! empty($changes['name'])) {
            $changeName = $this->conn->quoteIdentifier($changes['name'], true);
            $result = $this->conn->exec('ALTER TABLE ' . $name . ' RENAME TO ' . $changeName);
        }
    }

    /**
     * create sequence
     *
     * @param string $seqName name of the sequence to be created
     * @param string $start start value of the sequence; default is 1
     * @param array     $options  An associative array of table options:
     *                          array(
     *                              'comment' => 'Foo',
     *                              'charset' => 'utf8',
     *                              'collate' => 'utf8_unicode_ci',
     *                          );
     * @return string
     */
    public function createSequenceSql($seqName, $start = 1, array $options = array())
    {
        $sequenceName = $this->conn->quoteIdentifier($this->conn->formatter->getSequenceName($seqName), true);
        $query  = 'CREATE SEQUENCE ' . $sequenceName . ' START WITH ' . $start . ' INCREMENT BY 1 NOCACHE';
        $query .= ($start < 1 ? ' MINVALUE ' . $start : '');
        return $query;
    }

    /**
     * drop existing sequence
     *
     * @param object $this->conn database object that is extended by this class
     * @param string $seqName name of the sequence to be dropped
     * @return string
     */
    public function dropSequenceSql($seqName)
    {
        $sequenceName = $this->conn->quoteIdentifier($this->conn->formatter->getSequenceName($seqName), true);
        return 'DROP SEQUENCE ' . $sequenceName;
    }

    /**
     * return Oracle's SQL code portion needed to set an index
     * declaration to be unsed in statements like CREATE TABLE.
     * 
     * @param string $name      name of the index
     * @param array $definition index definition
     * @return string           Oracle's SQL code portion needed to set an index  
     */    
    public function getIndexDeclaration($name, array $definition)
    {
        $name = $this->conn->quoteIdentifier($name);
        $type = '';
        
        if ( isset($definition['type']))
        {
            if (strtolower($definition['type']) == 'unique') {
                $type = strtoupper($definition['type']);
            } else {
                throw new Doctrine_Export_Exception(
                    'Unknown type '.$definition['type'] .' for index '.$name
                );
            }
        } else {
            // only unique indexes should be defined in create table statement
            return null;
        }
        
        if ( !isset($definition['fields']) || !is_array($definition['fields'])) {
            throw new Doctrine_Export_Exception('No columns given for index '.$name);
        }
        
        $query = 'CONSTRAINT '.$name.' '.$type.' ('.$this->getIndexFieldDeclarationList($definition['fields']).')';
        
        return $query;
    }
}
