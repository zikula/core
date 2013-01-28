<?php

use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;

class Zikula_Session_LegacyHandler extends NativeSessionHandler
{
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

        $result = DBUtil::selectObjectByID('session_info', $sessionId, 'sessid');
        if (!$result) {
            return false;
        }

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

        /** @var $session Zikula_Session */
        $session = ServiceUtil::get('session');

        $obj = $session->get('obj');
        $obj['sessid'] = $sessionId;
        $obj['vars'] = $vars;
        $obj['remember'] = $session->get('rememberme', 0);
        $obj['uid'] = $session->get('uid', 0);
        $obj['ipaddr'] = $session->get('obj/ipaddr');
        $obj['lastused'] = date('Y-m-d H:i:s', $session->getMetadataBag()->getLastUsed());

        $result = DBUtil::selectObjectByID('session_info', $sessionId, 'sessid');
        if (!$result) {
            $res = DBUtil::insertObject($obj, 'session_info', 'sessid', true);
        } else {
            // check for regenerated session and update ID in database
            $sessiontable = DBUtil::getTables();
            $columns = $sessiontable['session_info_column'];
            $where = "WHERE $columns[sessid] = '".$sessionId."'";
            $res = DBUtil::updateObject($obj, 'session_info', $where, 'sessid', true, true);
        }

        return (bool) $res;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        // expire the cookie
        setcookie(session_name(), '', 0, ini_get('session.cookie_path'));

        $res = DBUtil::deleteObjectByID('session_info', $sessionId, 'sessid');
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        $now = time();
        $inactive = ($now - (int) (System::getVar('secinactivemins') * 60));
        $daysold = ($now - (int) (System::getVar('secmeddays') * 86400));


        // DB based GC
        $dbtable = DBUtil::getTables();
        $sessioninfocolumn = $dbtable['session_info_column'];
        $inactive = DataUtil::formatForStore(date('Y-m-d H:i:s', $inactive));
        $daysold = DataUtil::formatForStore(date('Y-m-d H:i:s', $daysold));

        switch (System::getVar('seclevel')) {
            case 'Low':
                // Low security - delete session info if user decided not to
                //                remember themself and inactivity timeout
                $where = "WHERE $sessioninfocolumn[remember] = 0
                          AND $sessioninfocolumn[lastused] < '$inactive'";
                break;
            case 'Medium':
                // Medium security - delete session info if session cookie has
                // expired or user decided not to remember themself and inactivity timeout
                // OR max number of days have elapsed without logging back in
                $where = "WHERE ($sessioninfocolumn[remember] = 0
                          AND $sessioninfocolumn[lastused] < '$inactive')
                          OR ($sessioninfocolumn[lastused] < '$daysold')
                          OR ($sessioninfocolumn[uid] = 0 AND $sessioninfocolumn[lastused] < '$inactive')";
                break;
            case 'High':
            default:
                // High security - delete session info if user is inactive
                $where = "WHERE $sessioninfocolumn[lastused] < '$inactive'";
                break;
        }

        $res = DBUtil::deleteWhere('session_info', $where);

        return (bool) $res;
    }

}
