<?php
/*
 *  $Id: Statement.php 1532 2007-05-31 17:45:07Z zYne $
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
 * Doctrine_Connection_Statement
 *
 * @package     Doctrine
 * @subpackage  Connection
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 1532 $
 */
class Doctrine_Connection_Statement implements Doctrine_Adapter_Statement_Interface
{
    /**
     * @var Doctrine_Connection $conn       Doctrine_Connection object, every connection
     *                                      statement holds an instance of Doctrine_Connection
     */
    protected $_conn;

    /**
     * @var mixed $_stmt                    PDOStatement object, boolean false or Doctrine_Adapter_Statement object
     */
    protected $_stmt;

    /**
     * constructor
     *
     * @param Doctrine_Connection $conn     Doctrine_Connection object, every connection
     *                                      statement holds an instance of Doctrine_Connection
     * @param mixed $stmt
     */
    public function __construct(Doctrine_Connection $conn, $stmt)
    {
        $this->_conn = $conn;
        $this->_stmt = $stmt;

        if ($stmt === false) {
            throw new Doctrine_Exception('Unknown statement object given.');
        }
    }

    /**
     * getConnection
     * returns the connection object this statement uses
     *
     * @return Doctrine_Connection
     */
    public function getConnection()
    {
        return $this->_conn;
    }

    public function getStatement()
    {
        return $this->_stmt;
    }

    public function getQuery()
    {
        return $this->_stmt->queryString;
    }

    /**
     * bindColumn
     * Bind a column to a PHP variable
     *
     * @param mixed $column         Number of the column (1-indexed) or name of the column in the result set.
     *                              If using the column name, be aware that the name should match
     *                              the case of the column, as returned by the driver.
     *
     * @param string $param         Name of the PHP variable to which the column will be bound.
     * @param integer $type         Data type of the parameter, specified by the Doctrine_Core::PARAM_* constants.
     * @return boolean              Returns TRUE on success or FALSE on failure
     */
    public function bindColumn($column, $param, $type = null)
    {
        if ($type === null) {
            return $this->_stmt->bindColumn($column, $param);
        } else {
            return $this->_stmt->bindColumn($column, $param, $type);
        }
    }

    /**
     * bindValue
     * Binds a value to a corresponding named or question mark
     * placeholder in the SQL statement that was use to prepare the statement.
     *
     * @param mixed $param          Parameter identifier. For a prepared statement using named placeholders,
     *                              this will be a parameter name of the form :name. For a prepared statement
     *                              using question mark placeholders, this will be the 1-indexed position of the parameter
     *
     * @param mixed $value          The value to bind to the parameter.
     * @param integer $type         Explicit data type for the parameter using the Doctrine_Core::PARAM_* constants.
     *
     * @return boolean              Returns TRUE on success or FALSE on failure.
     */
    public function bindValue($param, $value, $type = null)
    {
        if ($type === null) {
            return $this->_stmt->bindValue($param, $value);
        } else {
            return $this->_stmt->bindValue($param, $value, $type);
        }
    }

    /**
     * bindParam
     * Binds a PHP variable to a corresponding named or question mark placeholder in the
     * SQL statement that was use to prepare the statement. Unlike Doctrine_Adapter_Statement_Interface->bindValue(),
     * the variable is bound as a reference and will only be evaluated at the time
     * that Doctrine_Adapter_Statement_Interface->execute() is called.
     *
     * Most parameters are input parameters, that is, parameters that are
     * used in a read-only fashion to build up the query. Some drivers support the invocation
     * of stored procedures that return data as output parameters, and some also as input/output
     * parameters that both send in data and are updated to receive it.
     *
     * @param mixed $param          Parameter identifier. For a prepared statement using named placeholders,
     *                              this will be a parameter name of the form :name. For a prepared statement
     *                              using question mark placeholders, this will be the 1-indexed position of the parameter
     *
     * @param mixed $variable       Name of the PHP variable to bind to the SQL statement parameter.
     *
     * @param integer $type         Explicit data type for the parameter using the Doctrine_Core::PARAM_* constants. To return
     *                              an INOUT parameter from a stored procedure, use the bitwise OR operator to set the
     *                              Doctrine_Core::PARAM_INPUT_OUTPUT bits for the data_type parameter.
     *
     * @param integer $length       Length of the data type. To indicate that a parameter is an OUT parameter
     *                              from a stored procedure, you must explicitly set the length.
     * @param mixed $driverOptions
     * @return boolean              Returns TRUE on success or FALSE on failure.
     */
    public function bindParam($column, &$variable, $type = null, $length = null, $driverOptions = array())
    {
        if ($type === null) {
            return $this->_stmt->bindParam($column, $variable);
        } else {
            return $this->_stmt->bindParam($column, $variable, $type, $length, $driverOptions);
        }
    }

    /**
     * closeCursor
     * Closes the cursor, enabling the statement to be executed again.
     *
     * @return boolean              Returns TRUE on success or FALSE on failure.
     */
    public function closeCursor()
    {
        return $this->_stmt->closeCursor();
    }

    /**
     * columnCount
     * Returns the number of columns in the result set
     *
     * @return integer              Returns the number of columns in the result set represented
     *                              by the Doctrine_Adapter_Statement_Interface object. If there is no result set,
     *                              this method should return 0.
     */
    public function columnCount()
    {
        return $this->_stmt->columnCount();
    }

    /**
     * errorCode
     * Fetch the SQLSTATE associated with the last operation on the statement handle
     *
     * @see Doctrine_Adapter_Interface::errorCode()
     * @return string       error code string
     */
    public function errorCode()
    {
        return $this->_stmt->errorCode();
    }

    /**
     * errorInfo
     * Fetch extended error information associated with the last operation on the statement handle
     *
     * @see Doctrine_Adapter_Interface::errorInfo()
     * @return array        error info array
     */
    public function errorInfo()
    {
        return $this->_stmt->errorInfo();
    }

    /**
     * execute
     * Executes a prepared statement
     *
     * If the prepared statement included parameter markers, you must either:
     * call PDOStatement->bindParam() to bind PHP variables to the parameter markers:
     * bound variables pass their value as input and receive the output value,
     * if any, of their associated parameter markers or pass an array of input-only
     * parameter values
     *
     *
     * @param array $params             An array of values with as many elements as there are
     *                                  bound parameters in the SQL statement being executed.
     * @return boolean                  Returns TRUE on success or FALSE on failure.
     */
    public function execute($params = null)
    {
        try {
            $event = new Doctrine_Event($this, Doctrine_Event::STMT_EXECUTE, $this->getQuery(), $params);
            $this->_conn->getListener()->preStmtExecute($event);

            $result = true;
            if ( ! $event->skipOperation) {

                if ($this->_conn->getAttribute(Doctrine_Core::ATTR_PORTABILITY) & Doctrine_Core::PORTABILITY_EMPTY_TO_NULL) {
                    foreach ($params as $key => $value) {
                        if ($value === '') {
                            $params[$key] = null;
                        }
                    }
                }

                if ($params) {
                    $pos = 0;
                    foreach ($params as $key => $value) {
                        $pos++;
                        $param = is_numeric($key) ?  $pos : $key;
                        if (is_resource($value)) {
                            $this->_stmt->bindParam($param, $params[$key], Doctrine_Core::PARAM_LOB);
                        } else {
                            $this->_stmt->bindParam($param, $params[$key]);
                        }
                    }
                }

                $result = $this->_stmt->execute();

                $this->_conn->incrementQueryCount();
            }

            $this->_conn->getListener()->postStmtExecute($event);

            return $result;
        } catch (PDOException $e) {
        } catch (Doctrine_Adapter_Exception $e) {
        }

        $this->_conn->rethrowException($e, $this);

        return false;
    }

    /**
     * fetch
     *
     * @see Doctrine_Core::FETCH_* constants
     * @param integer $fetchStyle           Controls how the next row will be returned to the caller.
     *                                      This value must be one of the Doctrine_Core::FETCH_* constants,
     *                                      defaulting to Doctrine_Core::FETCH_BOTH
     *
     * @param integer $cursorOrientation    For a PDOStatement object representing a scrollable cursor,
     *                                      this value determines which row will be returned to the caller.
     *                                      This value must be one of the Doctrine_Core::FETCH_ORI_* constants, defaulting to
     *                                      Doctrine_Core::FETCH_ORI_NEXT. To request a scrollable cursor for your
     *                                      Doctrine_Adapter_Statement_Interface object,
     *                                      you must set the Doctrine_Core::ATTR_CURSOR attribute to Doctrine_Core::CURSOR_SCROLL when you
     *                                      prepare the SQL statement with Doctrine_Adapter_Interface->prepare().
     *
     * @param integer $cursorOffset         For a Doctrine_Adapter_Statement_Interface object representing a scrollable cursor for which the
     *                                      $cursorOrientation parameter is set to Doctrine_Core::FETCH_ORI_ABS, this value specifies
     *                                      the absolute number of the row in the result set that shall be fetched.
     *
     *                                      For a Doctrine_Adapter_Statement_Interface object representing a scrollable cursor for
     *                                      which the $cursorOrientation parameter is set to Doctrine_Core::FETCH_ORI_REL, this value
     *                                      specifies the row to fetch relative to the cursor position before
     *                                      Doctrine_Adapter_Statement_Interface->fetch() was called.
     *
     * @return mixed
     */
    public function fetch($fetchMode = Doctrine_Core::FETCH_BOTH,
                          $cursorOrientation = Doctrine_Core::FETCH_ORI_NEXT,
                          $cursorOffset = null)
    {
        $event = new Doctrine_Event($this, Doctrine_Event::STMT_FETCH, $this->getQuery());

        $event->fetchMode = $fetchMode;
        $event->cursorOrientation = $cursorOrientation;
        $event->cursorOffset = $cursorOffset;

        $data = $this->_conn->getListener()->preFetch($event);

        if ( ! $event->skipOperation) {
            $data = $this->_stmt->fetch($fetchMode, $cursorOrientation, $cursorOffset);
        }

        $this->_conn->getListener()->postFetch($event);

        return $data;
    }

    /**
     * fetchAll
     * Returns an array containing all of the result set rows
     *
     * @param integer $fetchMode            Controls how the next row will be returned to the caller.
     *                                      This value must be one of the Doctrine_Core::FETCH_* constants,
     *                                      defaulting to Doctrine_Core::FETCH_BOTH
     *
     * @param integer $columnIndex          Returns the indicated 0-indexed column when the value of $fetchStyle is
     *                                      Doctrine_Core::FETCH_COLUMN. Defaults to 0.
     *
     * @return array
     */
    public function fetchAll($fetchMode = Doctrine_Core::FETCH_BOTH,
                             $columnIndex = null)
    {
        $event = new Doctrine_Event($this, Doctrine_Event::STMT_FETCHALL, $this->getQuery());
        $event->fetchMode = $fetchMode;
        $event->columnIndex = $columnIndex;

        $this->_conn->getListener()->preFetchAll($event);

        if ( ! $event->skipOperation) {
            if ($columnIndex !== null) {
                $data = $this->_stmt->fetchAll($fetchMode, $columnIndex);
            } else {
                $data = $this->_stmt->fetchAll($fetchMode);
            }

            $event->data = $data;
        }

        $this->_conn->getListener()->postFetchAll($event);

        return $data;
    }

    /**
     * fetchColumn
     * Returns a single column from the next row of a
     * result set or FALSE if there are no more rows.
     *
     * @param integer $columnIndex          0-indexed number of the column you wish to retrieve from the row. If no
     *                                      value is supplied, Doctrine_Adapter_Statement_Interface->fetchColumn()
     *                                      fetches the first column.
     *
     * @return string                       returns a single column in the next row of a result set.
     */
    public function fetchColumn($columnIndex = 0)
    {
        return $this->_stmt->fetchColumn($columnIndex);
    }

    /**
     * fetchObject
     * Fetches the next row and returns it as an object.
     *
     * Fetches the next row and returns it as an object. This function is an alternative to
     * Doctrine_Adapter_Statement_Interface->fetch() with Doctrine_Core::FETCH_CLASS or Doctrine_Core::FETCH_OBJ style.
     *
     * @param string $className             Name of the created class, defaults to stdClass.
     * @param array $args                   Elements of this array are passed to the constructor.
     *
     * @return mixed                        an instance of the required class with property names that correspond
     *                                      to the column names or FALSE in case of an error.
     */
    public function fetchObject($className = 'stdClass', $args = array())
    {
        return $this->_stmt->fetchObject($className, $args);
    }

    /**
     * getAttribute
     * Retrieve a statement attribute
     *
     * @param integer $attribute
     * @see Doctrine_Core::ATTR_* constants
     * @return mixed                        the attribute value
     */
    public function getAttribute($attribute)
    {
        return $this->_stmt->getAttribute($attribute);
    }

    /**
     * getColumnMeta
     * Returns metadata for a column in a result set
     *
     * @param integer $column               The 0-indexed column in the result set.
     *
     * @return array                        Associative meta data array with the following structure:
     *
     *          native_type                 The PHP native type used to represent the column value.
     *          driver:decl_                type The SQL type used to represent the column value in the database. If the column in the result set is the result of a function, this value is not returned by PDOStatement->getColumnMeta().
     *          flags                       Any flags set for this column.
     *          name                        The name of this column as returned by the database.
     *          len                         The length of this column. Normally -1 for types other than floating point decimals.
     *          precision                   The numeric precision of this column. Normally 0 for types other than floating point decimals.
     *          pdo_type                    The type of this column as represented by the PDO::PARAM_* constants.
     */
    public function getColumnMeta($column)
    {
        return $this->_stmt->getColumnMeta($column);
    }

    /**
     * nextRowset
     * Advances to the next rowset in a multi-rowset statement handle
     *
     * Some database servers support stored procedures that return more than one rowset
     * (also known as a result set). The nextRowset() method enables you to access the second
     * and subsequent rowsets associated with a PDOStatement object. Each rowset can have a
     * different set of columns from the preceding rowset.
     *
     * @return boolean                      Returns TRUE on success or FALSE on failure.
     */
    public function nextRowset()
    {
        return $this->_stmt->nextRowset();
    }

    /**
     * rowCount
     * rowCount() returns the number of rows affected by the last DELETE, INSERT, or UPDATE statement
     * executed by the corresponding object.
     *
     * If the last SQL statement executed by the associated Statement object was a SELECT statement,
     * some databases may return the number of rows returned by that statement. However,
     * this behaviour is not guaranteed for all databases and should not be
     * relied on for portable applications.
     *
     * @return integer                      Returns the number of rows.
     */
    public function rowCount()
    {
        return $this->_stmt->rowCount();
    }

    /**
     * setAttribute
     * Set a statement attribute
     *
     * @param integer $attribute
     * @param mixed $value                  the value of given attribute
     * @return boolean                      Returns TRUE on success or FALSE on failure.
     */
    public function setAttribute($attribute, $value)
    {
        return $this->_stmt->setAttribute($attribute, $value);
    }

    /**
     * setFetchMode
     * Set the default fetch mode for this statement
     *
     * @param integer $mode                 The fetch mode must be one of the Doctrine_Core::FETCH_* constants.
     * @return boolean                      Returns 1 on success or FALSE on failure.
     */
    public function setFetchMode($mode, $arg1 = null, $arg2 = null)
    {
        return $this->_stmt->setFetchMode($mode, $arg1, $arg2);
    }
}
