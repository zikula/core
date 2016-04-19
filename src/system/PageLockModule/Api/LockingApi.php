<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PageLockModule\Api;

use PageUtil;
use Twig_Environment;
use Zikula\PageLockModule\Entity\PageLockEntity;

/**
 * Class LockingApi.
 * @package Zikula\PageLockModule\Api
 *
 * This class provides means for using a locking mechanism.
 * It should be used instead of the old user api.
 */
class LockingApi
{
    /**
     * length of time to lock a page
     */
    const PAGELOCKLIFETIME = 30;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * LockingApi constructor.
     *
     * @param Twig_Environment $twig Twig service instance.
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Add the page locking code to the page header
     *
     * @param string $lockName        The name of the lock to be released
     * @param string $returnUrl       The URL to return control to (optional) (default: null)
     * @param bool   $ignoreEmptyLock Ignore an empty lock name (optional) (default: false)
     *
     * @return bool true
     */
    public function pageLock($lockName, $returnUrl = null, $ignoreEmptyLock = false)
    {
        $hasLock = true;
        $lockedHtml = '';

        if (!empty($lockName) || !$ignoreEmptyLock) {
            PageUtil::addVar('javascript', 'zikula.ui');
            PageUtil::addVar('javascript', 'system/PageLockModule/Resources/public/js/PageLock.js');
            PageUtil::addVar('stylesheet', \ThemeUtil::getModuleStylesheet('ZikulaPageLockModule'));

            $lockInfo = $this->requireLock($lockName, \UserUtil::getVar('uname'), $_SERVER['REMOTE_ADDR']);

            $hasLock = $lockInfo['hasLock'];
            if (!$hasLock) {
                $templateParameters = [
                    'lockedBy' => $lockInfo['lockedBy']
                ];
                $lockedHtml = $this->twig->render('@ZikulaPageLockModule/lockedWindow.html.twig', $templateParameters);
            }
        }

        $html = "<script type=\"text/javascript\">/* <![CDATA[ */ \n";
        $html .= "( function($) {\n";

        if (!empty($lockName)) {
            if ($hasLock) {
                $html .= "    $(document).ready(PageLock.UnlockedPage);\n";
            } else {
                $html .= "    $(document).ready(PageLock.LockedPage);\n";
            }
        }

        $html .= "})(jQuery);\n";

        // Use "self::PAGELOCKLIFETIME*2/3" to add a good margin to lock timeout when pinging

        // disabled due to #2556 and #2745
        // $returnUrl = \DataUtil::formatForDisplayHTML($returnUrl);

        $html .= "
PageLock.LockName = '$lockName';
PageLock.ReturnUrl = '$returnUrl';
PageLock.PingTime = " . (self::PAGELOCKLIFETIME * 2 / 3) . ";
 /* ]]> */</script>";

        PageUtil::addVar('header', $html);
        PageUtil::addVar('footer', $lockedHtml);

        return true;
    }

    /**
     * Generate a lock on a page
     *
     * @param string $lockName      The name of the page to create/update a lock on.
     * @param string $lockedByTitle Name of user owning the current lock.
     * @param string $lockedByIPNo  Ip address of user owning the current lock.
     * @param string $sessionId     The ID of the session owning the lock (optional) (default: current session ID)
     *
     * @return ['haslock' => true if this user has a lock, false otherwise,
     *          'lockedBy' => if 'haslock' is false then the user who has the lock, null otherwise]
     */
    public function requireLock($lockName, $lockedByTitle, $lockedByIPNo, $sessionId = '')
    {
        $theSessionId = $sessionId != '' ? $sessionId : session_id();

        $this->requireAccess();

        $locks = $this->getLocks($lockName, $sessionId);
        if (count($locks) > 0) {
            $lockedBy = '';
            foreach ($locks as $lock) {
                if (strlen($lockedBy) > 0) {
                    $lockedBy .= ', ';
                }
                $lockedBy .= $lock['title'] . " ($lock[ipno]) " . $lock['cdate']->format('Y-m-d H:m:s');
            }

            return ['hasLock' => false, 'lockedBy' => $lockedBy];
        }

        // Look for existing lock
        $count = $this->getRepository()->getLockAmount($lockName, $theSessionId);

        $now = time();
        $expireDate = $now + self::PAGELOCKLIFETIME;

        if ($count > 0) {
            // update the existing lock with a new expiry date
            $this->getRepository()->updateExpireDate($lockName, $theSessionId, $expireDate);
        } else {
            // create the new object
            $newLock = new PageLockEntity();
            $newLock->setName($lockName);
            $newLock->setCdate(new \DateTime(\DateUtil::getDatetime($now)));
            $newLock->setEdate(new \DateTime(\DateUtil::getDatetime($expireDate)));
            $newLock->setSession($theSessionId);
            $newLock->setTitle($lockedByTitle);
            $newLock->setIpno($lockedByIPNo);
            // write this back to the db
            $this->entityManager->persist($newLock);
        }
        $this->entityManager->flush();

        $this->releaseAccess();

        return ['hasLock' => true];
    }

    /**
     * Get all the locks for a given page
     *
     * @param string $lockName  The name of the page to return locks for
     * @param string $sessionId The ID of the session owning the lock (optional) (default: current session ID)
     *
     * @return array array of locks for $lockName
     */
    public function getLocks($lockName, $sessionId = '')
    {
        $theSessionId = $sessionId != '' ? $sessionId : session_id();

        $this->requireAccess();

        // remove expired locks
        $this->getRepository()->deleteExpiredLocks();

        // get remaining active locks
        $locks = $this->getRepository()->getActiveLocks($lockName, $theSessionId);

        $this->releaseAccess();

        return $locks;
    }

    /**
     * Releases a lock on a page
     *
     * @param string $lockName  The name of the lock to be released
     * @param string $sessionId The ID of the session owning the lock (optional) (default: current session ID)
     *
     * @return bool true
     */
    public function releaseLock($lockName, $sessionId = '')
    {
        $theSessionId = $sessionId != '' ? $sessionId : session_id();

        $this->requireAccess();

        $this->getRepository()->deleteByLockName($lockName, $theSessionId);

        $this->releaseAccess();

        return true;
    }

    /**
     * Returns repository for the page lock entities.
     *
     * @return Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->getDoctrine()->getManager()
            ->getRepository('ZikulaPageLockModule:PageLockEntity');
    }

    /**
     * Internal locking mechanism to avoid concurrency inside the PageLock functions.
     *
     * @return void
     */
    private function requireAccess()
    {
        global $pageLockAccessCount;
        if (null === $pageLockAccessCount) {
            $pageLockAccessCount = 0;
        }

        if ($pageLockAccessCount == 0) {
            global $pageLockFile;
            $ostemp = \DataUtil::formatForOS(\ServiceUtil::get('service_container')->getParameter('temp_dir'));
            $pageLockFile = fopen($ostemp . '/pagelock.lock', 'w+');
            flock($pageLockFile, LOCK_EX);
            fwrite($pageLockFile, 'This is a locking file for synchronizing access to the PageLock module. Please do not delete.');
            fflush($pageLockFile);
        }

        ++$pageLockAccessCount;
    }

    /**
     * Internal locking mechanism to avoid concurrency inside the PageLock functions.
     *
     * @return void
     */
    private function releaseAccess()
    {
        global $pageLockAccessCount;

        --$pageLockAccessCount;

        if ($pageLockAccessCount == 0) {
            global $pageLockFile;
            flock($pageLockFile, LOCK_UN);
            fclose($pageLockFile);
        }
    }
}
