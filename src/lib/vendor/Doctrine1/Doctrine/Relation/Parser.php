<?php
/*
 *  $Id: Table.php 1397 2007-05-19 19:54:15Z zYne $
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
 * Doctrine_Relation_Parser
 *
 * @package     Doctrine
 * @subpackage  Relation
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version     $Revision: 1397 $
 * @link        www.doctrine-project.org
 * @since       1.0
 * @todo Composite key support?
 */
class Doctrine_Relation_Parser 
{
    /**
     * @var Doctrine_Table $_table          the table object this parser belongs to
     */
    protected $_table;

    /**
     * @var array $_relations               an array containing all the Doctrine_Relation objects for this table
     */
    protected $_relations = array();

    /**
     * @var array $_pending                 relations waiting for parsing
     */
    protected $_pending   = array();

    /**
     * constructor
     *
     * @param Doctrine_Table $table         the table object this parser belongs to
     */
    public function __construct(Doctrine_Table $table) 
    {
        $this->_table = $table;
    }

    /**
     * getTable
     *
     * @return Doctrine_Table   the table object this parser belongs to
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * getPendingRelation
     *
     * @return array            an array defining a pending relation
     */
    public function getPendingRelation($name) 
    {
        if ( ! isset($this->_pending[$name])) {
            throw new Doctrine_Relation_Exception('Unknown pending relation ' . $name);
        }
        
        return $this->_pending[$name];
    }

    /**
     * getPendingRelations
     *
     * @return array            an array containing all the pending relations
     */
    public function getPendingRelations() 
    {
        return $this->_pending;
    }

    /**
     * unsetPendingRelations
     * Removes a relation. Warning: this only affects pending relations
     *
     * @param string            relation to remove
     */
    public function unsetPendingRelations($name) 
    {
       unset($this->_pending[$name]);
    }

    /**
     * Check if a relation alias exists
     *
     * @param string $name 
     * @return boolean $bool
     */
    public function hasRelation($name)
    {
        if ( ! isset($this->_pending[$name]) && ! isset($this->_relations[$name])) {
            return false;
        }
        
        return true;
    }

    /**
     * binds a relation
     *
     * @param string $name
     * @param string $field
     * @return void
     */
    public function bind($name, $options = array())
    {
        $e    = explode(' as ', $name);
        $e    = array_map('trim', $e);
        $name = $e[0];
        $alias = isset($e[1]) ? $e[1] : $name;

        if ( ! isset($options['type'])) {
            throw new Doctrine_Relation_Exception('Relation type not set.');
        }

        if ($this->hasRelation($alias)) {
            unset($this->_relations[$alias]);
            unset($this->_pending[$alias]);
        }

        $this->_pending[$alias] = array_merge($options, array('class' => $name, 'alias' => $alias));

        return $this->_pending[$alias];
    }

    /**
     * getRelation
     *
     * @param string $alias      relation alias
     */
    public function getRelation($alias, $recursive = true)
    {
        if (isset($this->_relations[$alias])) {
            return $this->_relations[$alias];
        }

        if (isset($this->_pending[$alias])) {
            $def = $this->_pending[$alias];
            $identifierColumnNames = $this->_table->getIdentifierColumnNames();
            $idColumnName = array_pop($identifierColumnNames);

            // check if reference class name exists
            // if it does we are dealing with association relation
            if (isset($def['refClass'])) {
                $def = $this->completeAssocDefinition($def);
                $localClasses = array_merge($this->_table->getOption('parents'), array($this->_table->getComponentName()));

                $backRefRelationName = isset($def['refClassRelationAlias']) ?
                        $def['refClassRelationAlias'] : $def['refClass'];
                if ( ! isset($this->_pending[$backRefRelationName]) && ! isset($this->_relations[$backRefRelationName])) {

                    $parser = $def['refTable']->getRelationParser();

                    if ( ! $parser->hasRelation($this->_table->getComponentName())) {
                        $parser->bind($this->_table->getComponentName(),
                                      array('type'    => Doctrine_Relation::ONE,
                                            'local'   => $def['local'],
                                            'foreign' => $idColumnName,
                                            'localKey' => true,
                                            ));
                    }

                    if ( ! $this->hasRelation($backRefRelationName)) {
                        if (in_array($def['class'], $localClasses)) {
                            $this->bind($def['refClass'] . " as " . $backRefRelationName, array(
                                    'type' => Doctrine_Relation::MANY,
                                    'foreign' => $def['foreign'],
                                    'local'   => $idColumnName));
                        } else {
                            $this->bind($def['refClass'] . " as " . $backRefRelationName, array(
                                    'type' => Doctrine_Relation::MANY,
                                    'foreign' => $def['local'],
                                    'local'   => $idColumnName));
                        }
                    }
                }
                if (in_array($def['class'], $localClasses)) {
                    $rel = new Doctrine_Relation_Nest($def);
                } else {
                    $rel = new Doctrine_Relation_Association($def);
                }
            } else {
                // simple foreign key relation
                $def = $this->completeDefinition($def);

                if (isset($def['localKey']) && $def['localKey']) {
                    $rel = new Doctrine_Relation_LocalKey($def);

                    // Automatically index for foreign keys
                    $foreign = (array) $def['foreign'];

                    foreach ($foreign as $fk) {
                        // Check if its already not indexed (primary key)
                        if ( ! $rel['table']->isIdentifier($rel['table']->getFieldName($fk))) {
                            $rel['table']->addIndex($fk, array('fields' => array($fk)));
                        }
                    }
                } else {
                    $rel = new Doctrine_Relation_ForeignKey($def);
                }
            }
            if (isset($rel)) {
                // unset pending relation
                unset($this->_pending[$alias]);
                $this->_relations[$alias] = $rel;
                return $rel;
            }
        }
        if ($recursive) {
            $this->getRelations();

            return $this->getRelation($alias, false);
        } else {
            throw new Doctrine_Table_Exception('Unknown relation alias ' . $alias);
        }
    }

    /**
     * getRelations
     * returns an array containing all relation objects
     *
     * @return array        an array of Doctrine_Relation objects
     */
    public function getRelations()
    {
        foreach ($this->_pending as $k => $v) {
            $this->getRelation($k);
        }

        return $this->_relations;
    }

    /**
     * getImpl
     * returns the table class of the concrete implementation for given template
     * if the given template is not a template then this method just returns the
     * table class for the given record
     *
     * @param string $template
     */
    public function getImpl($template)
    {
        $conn = $this->_table->getConnection();

        if (class_exists($template) && in_array('Doctrine_Template', class_parents($template))) {
            $impl = $this->_table->getImpl($template);

            if ($impl === null) {
                throw new Doctrine_Relation_Parser_Exception("Couldn't find concrete implementation for template " . $template);
            }
        } else {
            $impl = $template;
        }

        return $conn->getTable($impl);
    }

    /**
     * Completes the given association definition
     *
     * @param array $def    definition array to be completed
     * @return array        completed definition array
     */
    public function completeAssocDefinition($def)
    {
        $conn = $this->_table->getConnection();
        $def['table'] = $this->getImpl($def['class']);
        $def['localTable'] = $this->_table;
        $def['class'] = $def['table']->getComponentName();
        $def['refTable'] = $this->getImpl($def['refClass']);

        $id = $def['refTable']->getIdentifierColumnNames();

        if (count($id) > 1) {
            if ( ! isset($def['foreign'])) {
                // foreign key not set
                // try to guess the foreign key

                $def['foreign'] = ($def['local'] === $id[0]) ? $id[1] : $id[0];
            }
            if ( ! isset($def['local'])) {
                // foreign key not set
                // try to guess the foreign key
                $def['local'] = ($def['foreign'] === $id[0]) ? $id[1] : $id[0];
            }
        } else {

            if ( ! isset($def['foreign'])) {
                // foreign key not set
                // try to guess the foreign key

                $columns = $this->getIdentifiers($def['table']);

                $def['foreign'] = $columns;
            }
            if ( ! isset($def['local'])) {
                // local key not set
                // try to guess the local key
                $columns = $this->getIdentifiers($this->_table);

                $def['local'] = $columns;
            }
        }
        return $def;
    }

    /**
     * getIdentifiers
     * gives a list of identifiers from given table
     *
     * the identifiers are in format:
     * [componentName].[identifier]
     *
     * @param Doctrine_Table $table     table object to retrieve identifiers from
     */
    public function getIdentifiers(Doctrine_Table $table)
    {
        $componentNameToLower = strtolower($table->getComponentName());
        if (is_array($table->getIdentifier())) {
            $columns = array();
            foreach ((array) $table->getIdentifierColumnNames() as $identColName) {
                $columns[] = $componentNameToLower . '_' . $identColName;
            }
        } else {
            $columns = $componentNameToLower . '_' . $table->getColumnName(
                    $table->getIdentifier());
        }

        return $columns;
    }

    /**
     * guessColumns
     *
     * @param array $classes                    an array of class names
     * @param Doctrine_Table $foreignTable      foreign table object
     * @return array                            an array of column names
     */
    public function guessColumns(array $classes, Doctrine_Table $foreignTable)
    {
        $conn = $this->_table->getConnection();

        foreach ($classes as $class) {
            try {
                $table   = $conn->getTable($class);
            } catch (Doctrine_Table_Exception $e) {
                continue;
            }
            $columns = $this->getIdentifiers($table);
            $found   = true;

            foreach ((array) $columns as $column) {
                if ( ! $foreignTable->hasColumn($column)) {
                    $found = false;
                    break;
                }
            }
            if ($found) {
                break;
            }
        }

        if ( ! $found) {
            throw new Doctrine_Relation_Exception("Couldn't find columns.");
        }

        return $columns;
    }

    /**
     * Completes the given definition
     *
     * @param array $def    definition array to be completed
     * @return array        completed definition array
     * @todo Description: What does it mean to complete a definition? What is done (not how)?
     *       Refactor (too long & nesting level)
     */
    public function completeDefinition($def)
    {
        $conn = $this->_table->getConnection();
        $def['table'] = $this->getImpl($def['class']);
        $def['localTable'] = $this->_table;
        $def['class'] = $def['table']->getComponentName();

        $foreignClasses = array_merge($def['table']->getOption('parents'), array($def['class']));
        $localClasses   = array_merge($this->_table->getOption('parents'), array($this->_table->getComponentName()));

        $localIdentifierColumnNames = $this->_table->getIdentifierColumnNames();
        $localIdentifierCount = count($localIdentifierColumnNames);
        $localIdColumnName = array_pop($localIdentifierColumnNames);
        $foreignIdentifierColumnNames = $def['table']->getIdentifierColumnNames();
        $foreignIdColumnName = array_pop($foreignIdentifierColumnNames);

        if (isset($def['local'])) {
            $def['local'] = $def['localTable']->getColumnName($def['local']);

            if ( ! isset($def['foreign'])) {
                // local key is set, but foreign key is not
                // try to guess the foreign key

                if ($def['local'] === $localIdColumnName) {
                    $def['foreign'] = $this->guessColumns($localClasses, $def['table']);
                } else {
                    // the foreign field is likely to be the
                    // identifier of the foreign class
                    $def['foreign'] = $foreignIdColumnName;
                    $def['localKey'] = true;
                }
            } else {
                $def['foreign'] = $def['table']->getColumnName($def['foreign']);

                if ($localIdentifierCount == 1) {
                    if ($def['local'] == $localIdColumnName && isset($def['owningSide'])
                            && $def['owningSide'] === true) {
                        $def['localKey'] = true;
                    } else if (($def['local'] !== $localIdColumnName && $def['type'] == Doctrine_Relation::ONE)) {
                        $def['localKey'] = true;
                    }
                } else if ($localIdentifierCount > 1 && ! isset($def['localKey'])) {
                    // It's a composite key and since 'foreign' can not point to a composite
                    // key currently, we know that 'local' must be the foreign key.
                    $def['localKey'] = true;
                }
            }
        } else {
            if (isset($def['foreign'])) {
                $def['foreign'] = $def['table']->getColumnName($def['foreign']);

                // local key not set, but foreign key is set
                // try to guess the local key
                if ($def['foreign'] === $foreignIdColumnName) {
                    $def['localKey'] = true;
                    try {
                        $def['local'] = $this->guessColumns($foreignClasses, $this->_table);
                    } catch (Doctrine_Relation_Exception $e) {
                        $def['local'] = $localIdColumnName;
                    }
                } else {
                    $def['local'] = $localIdColumnName;
                }
            } else {
                // neither local or foreign key is being set
                // try to guess both keys

                $conn = $this->_table->getConnection();

                // the following loops are needed for covering inheritance
                foreach ($localClasses as $class) {
                    $table = $conn->getTable($class);
                    $identifierColumnNames = $table->getIdentifierColumnNames();
                    $idColumnName = array_pop($identifierColumnNames);
                    $column = strtolower($table->getComponentName())
                            . '_' . $idColumnName;

                    foreach ($foreignClasses as $class2) {
                        $table2 = $conn->getTable($class2);
                        if ($table2->hasColumn($column)) {
                            $def['foreign'] = $column;
                            $def['local'] = $idColumnName;
                            return $def;
                        }
                    }
                }

                foreach ($foreignClasses as $class) {
                    $table  = $conn->getTable($class);
                    $identifierColumnNames = $table->getIdentifierColumnNames();
                    $idColumnName = array_pop($identifierColumnNames);
                    $column = strtolower($table->getComponentName())
                            . '_' . $idColumnName;

                    foreach ($localClasses as $class2) {
                        $table2 = $conn->getTable($class2);
                        if ($table2->hasColumn($column)) {
                            $def['foreign']  = $idColumnName;
                            $def['local']    = $column;
                            $def['localKey'] = true;
                            return $def;
                        }
                    }
                }

                // auto-add columns and auto-build relation
                $columns = array();
                foreach ((array) $this->_table->getIdentifierColumnNames() as $id) {
                    // ?? should this not be $this->_table->getComponentName() ??
                    $column = strtolower($table->getComponentName())
                            . '_' . $id;

                    $col = $this->_table->getColumnDefinition($id);
                    $type = $col['type'];
                    $length = $col['length'];

                    unset($col['type']);
                    unset($col['length']);
                    unset($col['autoincrement']);
                    unset($col['sequence']);
                    unset($col['primary']);

                    $def['table']->setColumn($column, $type, $length, $col);

                    $columns[] = $column;
                }
                if (count($columns) > 1) {
                    $def['foreign'] = $columns;
                } else {
                    $def['foreign'] = $columns[0];
                }
                $def['local'] = $localIdColumnName;
            }
        }
        return $def;
    }
}