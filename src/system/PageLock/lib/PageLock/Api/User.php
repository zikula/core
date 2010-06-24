<?php
/**
 * Zikula Application Framework
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage PageLock
 */

/**
 * length of time to lock a page
 *
 */
define('PageLockLifetime', 30);

class PageLock_Api_User extends Zikula_Api
{
    public function pageLock($args)
    {
        $lockName = $args['lockName'];
        $returnUrl = (array_key_exists('returnUrl', $args) ? $args['returnUrl'] : null);
        $ignoreEmptyLock = (array_key_exists('ignoreEmptyLock', $args) ? $args['ignoreEmptyLock'] : false);

        $uname = UserUtil::getVar('uname');

        $lockedHtml = '';

        if (!empty($lockName) || !$ignoreEmptyLock) {
            PageUtil::AddVar('javascript', 'javascript/ajax/prototype.js');
            PageUtil::AddVar('javascript', 'javascript/ajax/scriptaculous.js');
            PageUtil::AddVar('javascript', 'javascript/ajax/ajax.js');
            PageUtil::AddVar('javascript', 'system/PageLock/javascript/pagelock.js');
            PageUtil::AddVar('stylesheet', ThemeUtil::getModuleStylesheet('PageLock'));

            $lockInfo = ModUtil::apiFunc('PageLock', 'user', 'requireLock',
                    array('lockName'      => $lockName,
                    'lockedByTitle' => $uname,
                    'lockedByIPNo'  => $_SERVER['REMOTE_ADDR']));

            $hasLock = $lockInfo['hasLock'];

            if (!$hasLock) {
                $renderer = Renderer::getInstance('PageLock');
                $renderer->assign('lockedBy', $lockInfo['lockedBy']);
                $lockedHtml = $renderer->fetch('PageLock_lockedwindow.tpl');
            }
        } else {
            $hasLock = true;
        }

        $html = "<script type=\"text/javascript\">\n";

        if (!empty($lockName))
        {
            if ($hasLock) {
                $html .= "Event.observe(window, 'load', PageLock.UnlockedPage);\n";
            } else {
                $html .= "Event.observe(window, 'load', PageLock.LockedPage);\n";
            }
        }

        $lockedHtml = str_replace("\n", "", $lockedHtml);
        $lockedHtml = str_replace("\r", "", $lockedHtml);

        // Use "PageLockLifetime*2/3" to add a good margin to lock timeout when pinging

        $returnUrl = DataUtil::formatForDisplayHTML($returnUrl);
        $html .= "
PageLock.LockName = '$lockName';
PageLock.BreakLockWarning = '" . __('Are you sure you want to break this lock?')  . "';
PageLock.ReturnUrl = '$returnUrl';
PageLock.PingTime = " . (PageLockLifetime*2/3) . ";
PageLock.LockedHTML = '" . $lockedHtml . "';
</script>";

        PageUtil::addVar('rawtext', $html);

        return true;
    }


    public function requireLock($args)
    {
        $lockName = $args['lockName'];
        $sessionId = (array_key_exists('sessionId', $args) ? $args['sessionId'] : session_id());
        $lockedByTitle = $args['lockedByTitle'];
        $lockedByIPNo = $args['lockedByIPNo'];

        $this->_pageLockRequireAccess();

        $locks = ModUtil::apiFunc('PageLock', 'user', 'getLocks',
                $args);
        if (count($locks) > 0) {
            $lockedBy = '';
            foreach ($locks as $lock) {
                if (strlen($lockedBy) > 0) {
                    $lockedBy .= ', ';
                }
                $lockedBy .= $lock['lockedByTitle'] . " ($lock[lockedByIPNo]) " . $lock['createdDate'];
            }

            return array('hasLock' => false, 'lockedBy' => $lockedBy);
        }

        $args['lockedBy'] = null;

        $pntable = System::dbGetTables();
        $pageLockTable = &$pntable['PageLock'];
        $pageLockColumn = &$pntable['PageLock_column'];

        // Look for existing lock

        $sql = "
SELECT COUNT(*)
FROM $pageLockTable
WHERE $pageLockColumn[name] = '" . DataUtil::formatForStore($lockName) . "' AND $pageLockColumn[lockedBySessionId] = '" . DataUtil::formatForStore($sessionId) . "'";

        $count = DBUtil::selectScalar($sql);

        $now = time();
        $expireDate = $now + PageLockLifetime;

        if ($count > 0) {
            // Update existing lock
            $sql = "
UPDATE $pageLockTable
SET $pageLockColumn[expiresDate] = '" . DateUtil::getDatetime($expireDate) . "'
WHERE $pageLockColumn[name] = '" . DataUtil::formatForStore($lockName) . "' AND $pageLockColumn[lockedBySessionId] = '" . DataUtil::formatForStore($sessionId) . "'";

            DBUtil::executeSql($sql);
        } else {
            $data = array('name' => $lockName,
                    'createdDate' => DateUtil::getDatetime($now),
                    'expiresDate' => DateUtil::getDatetime($expireDate),
                    'lockedBySessionId' => $sessionId,
                    'lockedByTitle' => $lockedByTitle,
                    'lockedByIPNo' => $lockedByIPNo);
            DBUtil::insertObject($data, 'PageLock');
        }

        $this->_pageLockReleaseAccess();

        return array('hasLock' => true);
    }


    public function getLocks($args)
    {
        $lockName = $args['lockName'];
        $sessionId = (array_key_exists('sessionId', $args) ? $args['sessionId'] : session_id());

        $this->_pageLockRequireAccess();

        $pntable = System::dbGetTables();
        $pageLockColumn = &$pntable['PageLock_column'];
        $now = time();

        $where = "{$pageLockColumn['expiresDate']} < '" . DateUtil::getDatetime($now) . "'";
        DBUtil::deleteWhere('PageLock', $where);

        $where = "{$pageLockColumn['name']} = '" . DataUtil::formatForStore($lockName) . "' AND {$pageLockColumn['lockedBySessionId']} != '" . DataUtil::formatForStore($sessionId) . "'";
        $locks = DBUtil::selectObjectArray('PageLock', $where);

        $this->_pageLockReleaseAccess();

        return $locks;
    }

    public function releaseLock($args)
    {
        $lockName = $args['lockName'];
        $sessionId = (array_key_exists('sessionId', $args) ? $args['sessionId'] : session_id());

        $this->_pageLockRequireAccess();

        $pntable = System::dbGetTables();
        $pageLockTable = &$pntable['PageLock'];
        $pageLockColumn = &$pntable['PageLock_column'];

        $sql = "DELETE FROM $pageLockTable WHERE $pageLockColumn[name] = '" . DataUtil::formatForStore($lockName) . "' AND $pageLockColumn[lockedBySessionId] = '" . DataUtil::formatForStore($sessionId) . "'";
        DBUtil::executeSql($sql);

        $this->_pageLockReleaseAccess();

        return true;
    }


// Internal locking mechanism to avoid concurrency inside the PageLock functions
    private function _pageLockRequireAccess()
    {
        global $PageLockAccessCount;
        if ($PageLockAccessCount == null) {
            $PageLockAccessCount = 0;
        }

        if ($PageLockAccessCount == 0) {
            global $PageLockFile;
            $ostemp = DataUtil::formatForOS(System::getVar('temp'), true);
            $PageLockFile = fopen($ostemp . '/pagelock.lock', "w+");
            flock($PageLockFile, LOCK_EX);
            fwrite($PageLockFile, "This is a locking file for synchronizing access to the PageLock module. Please do not delete.");
            fflush($PageLockFile);
        }

        ++$PageLockAccessCount;
    }


// Internal locking mechanism to avoid concurrency inside the PageLock functions
    private function _pageLockReleaseAccess()
    {
        global $PageLockAccessCount;

        --$PageLockAccessCount;

        if ($PageLockAccessCount == 0) {
            global $PageLockFile;
            flock($PageLockFile, LOCK_UN);
            fclose($PageLockFile);
        }
    }

}