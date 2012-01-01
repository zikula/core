<?php
/*
 *  $Id: Sqlite.php 7490 2010-03-29 19:53:27Z jwage $
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
 * Doctrine_Connection_Sqlite
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
class Doctrine_Connection_Sqlite extends Doctrine_Connection_Common
{
    /**
     * @var string $driverName                  the name of this connection driver
     */
    protected $driverName = 'Sqlite';

    /**
     * the constructor
     *
     * @param Doctrine_Manager $manager
     * @param PDO $pdo                          database handle
     */
    public function __construct(Doctrine_Manager $manager, $adapter)
    {
        $this->supported = array('sequences'            => 'emulated',
                          'indexes'              => true,
                          'affected_rows'        => true,
                          'summary_functions'    => true,
                          'order_by_text'        => true,
                          'current_id'           => 'emulated',
                          'limit_queries'        => true,
                          'LOBs'                 => true,
                          'replace'              => true,
                          'transactions'         => true,
                          'savepoints'           => false,
                          'sub_selects'          => true,
                          'auto_increment'       => true,
                          'primary_key'          => true,
                          'result_introspection' => false, // not implemented
                          'prepared_statements'  => 'emulated',
                          'identifier_quoting'   => true,
                          'pattern_escaping'     => false,
                          );
         parent::__construct($manager, $adapter);

        if ($this->isConnected) {
            $this->dbh->sqliteCreateFunction('mod',    array('Doctrine_Expression_Sqlite', 'modImpl'), 2);
            $this->dbh->sqliteCreateFunction('concat', array('Doctrine_Expression_Sqlite', 'concatImpl'));
            $this->dbh->sqliteCreateFunction('md5', 'md5', 1);
            $this->dbh->sqliteCreateFunction('now', array('Doctrine_Expression_Sqlite', 'nowImpl'), 0);
        }
    }

    /**
     * initializes database functions missing in sqlite
     *
     * @see Doctrine_Expression
     * @return void
     */
    public function connect() 
    {
        if ($this->isConnected) {
            return false;
        }

        parent::connect();

        $this->dbh->sqliteCreateFunction('mod',    array('Doctrine_Expression_Sqlite', 'modImpl'), 2);
        $this->dbh->sqliteCreateFunction('concat', array('Doctrine_Expression_Sqlite', 'concatImpl'));
        $this->dbh->sqliteCreateFunction('md5', 'md5', 1);
        $this->dbh->sqliteCreateFunction('now', array('Doctrine_Expression_Sqlite', 'nowImpl'), 0);
    }

    /**
     * createDatabase
     *
     * @return void
     */
    public function createDatabase()
    {
        if ( ! $dsn = $this->getOption('dsn')) {
            throw new Doctrine_Connection_Exception('You must create your Doctrine_Connection by using a valid Doctrine style dsn in order to use the create/drop database functionality');
        }

        $info = $this->getManager()->parseDsn($dsn);

        $this->export->createDatabase($info['database']);
    }

    /**
     * dropDatabase
     *
     * @return void
     */
    public function dropDatabase()
    {
        if ( ! $dsn = $this->getOption('dsn')) {
            throw new Doctrine_Connection_Exception('You must create your Doctrine_Connection by using a valid Doctrine style dsn in order to use the create/drop database functionality');
        }
        
        $info = $this->getManager()->parseDsn($dsn);

        $this->export->dropDatabase($info['database']);
    }
}