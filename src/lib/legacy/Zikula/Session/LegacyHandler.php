<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Connection;

/**
 * Class Zikula_Session_LegacyHandler
 *
 * @deprecated
 */
class Zikula_Session_LegacyHandler implements \SessionHandlerInterface
{
    /**
     * @var Zikula_Session_Storage_Legacy
     */
    private $storage;

    /**
     * @var Connection
     */
    private $conn;

    public function setStorage(Zikula_Session_Storage_Legacy $storage)
    {
        $this->storage = $storage;
    }

    public function setConnection(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        if (System::isInstalling()) {
            return '';
        }

        $result = $this->conn->executeQuery('SELECT vars FROM session_info WHERE sessid=:id', array('id' => $sessionId))->fetch();

        return isset($result['vars']) ? $result['vars'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $vars)
    {
        if (System::isInstalling()) {
            return true;
        }

        // http host is not given for CLI requests for example
        $ipDefault = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

        $obj = $this->storage->getBag('attributes')->get('obj');
        $obj['sessid'] = $sessionId;
        $obj['vars'] = $vars;
        $obj['remember'] = $this->storage->getBag('attributes')->get('rememberme', 0);
        $obj['uid'] = $this->storage->getBag('attributes')->get('uid', 0);
        $obj['ipaddr'] = $this->storage->getBag('attributes')->get('obj/ipaddr', $ipDefault);
        $obj['lastused'] = date('Y-m-d H:i:s', $this->storage->getMetadataBag()->getLastUsed());

        $query = $this->conn->executeQuery('SELECT * FROM session_info WHERE sessid=:id', array('id' => $sessionId));
        if (!$res = $query->fetch(\PDO::FETCH_ASSOC)) {
            $res = $this->conn->executeUpdate('INSERT INTO session_info (sessid, ipaddr, lastused, uid, remember, vars)
            VALUES (:sessid, :ipaddr, :lastused, :uid, :remember, :vars)',
                array(
                     'sessid' => $obj['sessid'],
                     'ipaddr' => $obj['ipaddr'],
                     'lastused' => $obj['lastused'],
                     'uid' => $obj['uid'],
                     'remember' => $obj['remember'],
                     'uid' => $obj['uid'],
                     'vars' => $obj['vars'],
                ));
        } else {
            // check for regenerated session and update ID in database
            $res = $this->conn->executeUpdate('UPDATE session_info SET ipaddr = :ipaddr, lastused = :lastused, uid = :uid, remember = :remember, vars = :vars WHERE sessid = :sessid',
                array(
                     'sessid' => $obj['sessid'],
                     'ipaddr' => $obj['ipaddr'],
                     'lastused' => $obj['lastused'],
                     'uid' => $obj['uid'],
                     'remember' => $obj['remember'],
                     'uid' => $obj['uid'],
                     'vars' => $obj['vars'],
                ));
        }

        return (bool) $res;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        // expire the cookie
        if (php_sapi_name() != 'cli') {
            setcookie(session_name(), '', 0, ini_get('session.cookie_path'));
        }

        $this->conn->executeUpdate('DELETE FROM session_info WHERE sessid=:id', array('id' => $sessionId));
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        $now = time();
        $inactive = ($now - (int) (System::getVar('secinactivemins') * 60));
        $daysold = ($now - (int) (System::getVar('secmeddays') * 86400));

        $inactive = date('Y-m-d H:i:s', $inactive);
        $daysold = date('Y-m-d H:i:s', $daysold);

        switch (System::getVar('seclevel')) {
            case 'Low':
                // Low security - delete session info if user decided not to
                //                remember themself and inactivity timeout
                $where = "WHERE remember = 0
                          AND lastused < '$inactive'";
                break;
            case 'Medium':
                // Medium security - delete session info if session cookie has
                // expired or user decided not to remember themself and inactivity timeout
                // OR max number of days have elapsed without logging back in
                $where = "WHERE (remember = 0
                          AND lastused < '$inactive')
                          OR (lastused < '$daysold')
                          OR (uid = 0 AND lastused < '$inactive')";
                break;
            case 'High':
            default:
                // High security - delete session info if user is inactive
                $where = "WHERE lastused < '$inactive'";
                break;
        }

        try {
            $res = $this->conn->executeUpdate('DELETE FROM session_info ' . $where);
        } catch (\Exception $e) {
            // silently fail
            $res = false;
        }

        return (bool) $res;
    }
}
