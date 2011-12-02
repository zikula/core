<?php
/*
 *  $Id$
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
 * Doctrine_Event
 *
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @package     Doctrine
 * @subpackage  Event
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Event
{
    /**
     * CONNECTION EVENT CODES
     */
    const CONN_QUERY         = 1;
    const CONN_EXEC          = 2;
    const CONN_PREPARE       = 3;
    const CONN_CONNECT       = 4;
    const CONN_CLOSE         = 5;
    const CONN_ERROR         = 6;

    const STMT_EXECUTE       = 10;
    const STMT_FETCH         = 11;
    const STMT_FETCHALL      = 12;

    const TX_BEGIN           = 31;
    const TX_COMMIT          = 32;
    const TX_ROLLBACK        = 33;
    const SAVEPOINT_CREATE   = 34;
    const SAVEPOINT_ROLLBACK = 35;
    const SAVEPOINT_COMMIT   = 36;

    const HYDRATE            = 40;

    /*
     * RECORD EVENT CODES
     */
    const RECORD_DELETE      = 21;
    const RECORD_SAVE        = 22;
    const RECORD_UPDATE      = 23;
    const RECORD_INSERT      = 24;
    const RECORD_SERIALIZE   = 25;
    const RECORD_UNSERIALIZE = 26;
    const RECORD_DQL_DELETE  = 27;
    const RECORD_DQL_SELECT  = 28;
    const RECORD_DQL_UPDATE  = 29;
    const RECORD_VALIDATE    = 30;

    /**
     * @var mixed $_nextSequence        the sequence of the next event that will be created
     */
    static protected $_nextSequence = 0;

    /**
     * @var mixed $_sequence            the sequence of this event
     */
    protected $_sequence;

    /**
     * @var mixed $_invoker             the handler which invoked this event
     */
    protected $_invoker;

    /**
     * @var string $_query              the sql query associated with this event (if any)
     */
    protected $_query;

    /**
     * @var string $_params             the parameters associated with the query (if any)
     */
    protected $_params;

    /**
     * @see Doctrine_Event constants
     * @var integer $_code              the event code
     */
    protected $_code;

    /**
     * @var integer $_startedMicrotime  the time point in which this event was started
     */
    protected $_startedMicrotime;

    /**
     * @var integer $_endedMicrotime    the time point in which this event was ended
     */
    protected $_endedMicrotime;

    /**
     * @var array $_options             an array of options
     */
    protected $_options = array();

    /**
     * constructor
     *
     * @param Doctrine_Connection|Doctrine_Connection_Statement|
              Doctrine_Connection_UnitOfWork|Doctrine_Transaction $invoker   the handler which invoked this event
     * @param integer $code                                                  the event code
     * @param string $query                                                  the sql query associated with this event (if any)
     */
    public function __construct($invoker, $code, $query = null, $params = array())
    {
        $this->_sequence = self::$_nextSequence++;
        $this->_invoker  = $invoker;
        $this->_code     = $code;
        $this->_query    = $query;
        $this->_params   = $params;
    }

    /**
     * getQuery
     *
     * @return Doctrine_Query       returns the query associated with this event (if any)
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * getName
     * returns the name of this event
     *
     * @return string       the name of this event
     */
    public function getName()
    {
        switch ($this->_code) {
            case self::CONN_QUERY:
                return 'query';
            case self::CONN_EXEC:
                return 'exec';
            case self::CONN_PREPARE:
                return 'prepare';
            case self::CONN_CONNECT:
                return 'connect';
            case self::CONN_CLOSE:
                return 'close';
            case self::CONN_ERROR:
                return 'error';

            case self::STMT_EXECUTE:
                return 'execute';
            case self::STMT_FETCH:
                return 'fetch';
            case self::STMT_FETCHALL:
                return 'fetch all';

            case self::TX_BEGIN:
                return 'begin';
            case self::TX_COMMIT:
                return 'commit';
            case self::TX_ROLLBACK:
                return 'rollback';

            case self::SAVEPOINT_CREATE:
                return 'create savepoint';
            case self::SAVEPOINT_ROLLBACK:
                return 'rollback savepoint';
            case self::SAVEPOINT_COMMIT:
                return 'commit savepoint';

            case self::RECORD_DELETE:
                return 'delete record';
            case self::RECORD_SAVE:
                return 'save record';
            case self::RECORD_UPDATE:
                return 'update record';
            case self::RECORD_INSERT:
                return 'insert record';
            case self::RECORD_SERIALIZE:
                return 'serialize record';
            case self::RECORD_UNSERIALIZE:
                return 'unserialize record';
            case self::RECORD_DQL_SELECT:
                return 'select records';
            case self::RECORD_DQL_DELETE:
                return 'delete records';
            case self::RECORD_DQL_UPDATE:
                return 'update records';
            case self::RECORD_VALIDATE:
                return 'validate record';
        }
    }

    /**
     * getCode
     *
     * @return integer      returns the code associated with this event
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * getOption
     * returns the value of an option
     *
     * @param string $option    the name of the option
     * @return mixed
     */
    public function __get($option)
    {
        if ( ! isset($this->_options[$option])) {
            return null;
        }

        return $this->_options[$option];
    }

    /**
     * skipOperation
     * skips the next operation
     * an alias for __set('skipOperation', true)
     *
     * @return Doctrine_Event   this object
     */
    public function skipOperation()
    {
        $this->_options['skipOperation'] = true;

        return $this;
    }

    /**
     * setOption
     * sets the value of an option
     *
     * @param string $option    the name of the option
     * @param mixed $value      the value of the given option
     * @return Doctrine_Event   this object
     */
    public function __set($option, $value)
    {
        $this->_options[$option] = $value;

        return $this;
    }

    /**
     * setOption
     * sets the value of an option by reference
     *
     * @param string $option    the name of the option
     * @param mixed $value      the value of the given option
     * @return Doctrine_Event   this object
     */
    public function set($option, &$value)
    {
        $this->_options[$option] =& $value;

        return $this;
    }

    /**
     * start
     * starts the internal timer of this event
     *
     * @return Doctrine_Event   this object
     */
    public function start()
    {
        $this->_startedMicrotime = microtime(true);
    }

    /**
     * hasEnded
     * whether or not this event has ended
     *
     * @return boolean
     */
    public function hasEnded()
    {
        return ($this->_endedMicrotime != null);
    }

    /**
     * end
     * ends the internal timer of this event
     *
     * @return Doctrine_Event   this object
     */
    public function end()
    {
        $this->_endedMicrotime = microtime(true);

        return $this;
    }

    /**
     * getSequence
     * returns the sequence of this event
     *
     * @return integer
     */
    public function getSequence()
    {
        return $this->_sequence;
    }

    /**
     * getInvoker
     * returns the handler that invoked this event
     *
     * @return Doctrine_Connection|Doctrine_Connection_Statement|
     *         Doctrine_Connection_UnitOfWork|Doctrine_Transaction   the handler that invoked this event
     */
    public function getInvoker()
    {
        return $this->_invoker;
    }

    /**
     * setInvoker
     * Defines new invoker (used in Hydrator)
     *
     * @param mixed $invoker
     * @return void
     */
    public function setInvoker($invoker)
    {
        $this->_invoker = $invoker;
    }

    /**
     * getParams
     * returns the parameters of the query
     *
     * @return array   parameters of the query
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Get the elapsed time (in microseconds) that the event ran.  If the event has
     * not yet ended, return false.
     *
     * @return integer
     */
    public function getElapsedSecs()
    {
        if (is_null($this->_endedMicrotime)) {
            return false;
        }
        return ($this->_endedMicrotime - $this->_startedMicrotime);
    }
}
