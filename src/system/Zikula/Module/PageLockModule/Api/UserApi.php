<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\PageLockModule\Api;

use UserUtil;
use PageUtil;
use ThemeUtil;
use ModUtil;
use Zikula_View;
use DataUtil;
use DateUtil;
use ServiceUtil;
use Zikula\Module\PageLockModule\Entity\PageLockEntity;

/**
 * API functions used by user controllers
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * length of time to lock a page
     *
     */
    const PAGELOCKLIFETIME = 30;

    /**
     * Add the page locking code to the page header
     *
     * @param mixed[] $args {
     *      @type string $lockName         The name of the lock to be released
     *      @type string $returnUrl        The URL to return control to (optional) (default: null)
     *      @type bool   $ignoreEmptyLock  Ignore an empty lock name (optional) (default: false)
     *                      }
     *
     * @return bool true
     */
    public function pageLock($args)
    {
        $lockName = $args['lockName'];
        $returnUrl = (array_key_exists('returnUrl', $args) ? $args['returnUrl'] : null);
        $ignoreEmptyLock = (array_key_exists('ignoreEmptyLock', $args) ? $args['ignoreEmptyLock'] : false);

        $uname = UserUtil::getVar('uname');

        $lockedHtml = '';

        if (!empty($lockName) || !$ignoreEmptyLock) {
            PageUtil::AddVar('javascript', 'zikula.ui');
            PageUtil::AddVar('javascript', 'system/Zikula/Module/PageLockModule/Resources/public/js/pagelock.js');
            PageUtil::AddVar('stylesheet', ThemeUtil::getModuleStylesheet('pagelock'));

            $lockInfo = ModUtil::apiFunc('ZikulaPageLockModule', 'user', 'requireLock',
                    array('lockName'      => $lockName,
                    'lockedByTitle' => $uname,
                    'lockedByIPNo'  => $_SERVER['REMOTE_ADDR']));

            $hasLock = $lockInfo['hasLock'];

            if (!$hasLock) {
                $view = Zikula_View::getInstance('pagelock');
                $view->assign('lockedBy', $lockInfo['lockedBy']);
                $lockedHtml = $view->fetch('PageLock_lockedwindow.tpl');
            }
        } else {
            $hasLock = true;
        }

        $html = "<script type=\"text/javascript\">/* <![CDATA[ */ \n";

        if (!empty($lockName)) {
            if ($hasLock) {
                $html .= "document.observe('dom:loaded', PageLock.UnlockedPage);\n";
            } else {
                $html .= "document.observe('dom:loaded', PageLock.LockedPage);\n";
            }
        }

        $lockedHtml = str_replace("\n", "", $lockedHtml);
        $lockedHtml = str_replace("\r", "", $lockedHtml);

        // Use "self::PAGELOCKLIFETIME*2/3" to add a good margin to lock timeout when pinging

        // disabled due to #2556 and #2745
        // $returnUrl = DataUtil::formatForDisplayHTML($returnUrl);

        $html .= "
PageLock.LockName = '$lockName';
PageLock.ReturnUrl = '$returnUrl';
PageLock.PingTime = " . (self::PAGELOCKLIFETIME*2/3) . ";
PageLock.LockedHTML = '" . $lockedHtml . "';
 /* ]]> */</script>";

        PageUtil::addVar('header', $html);

        return true;
    }

    /**
     * Generate a lock on a page
     *
     * @param string[] $args { 
     *      @type string $lockName   The name of the page to create/update a lock on
     *      @type string $sessionId  The ID of the session owning the lock (optional) (default: current session ID
     *                       }
     *
     * @return array('haslock' => true if this user has a lock, false otherwise,
     *                'lockedBy' => if 'haslock' is false then the user who has the lock, null otherwise)
     */
    public function requireLock($args)
    {
        $lockName = $args['lockName'];
        $sessionId = (array_key_exists('sessionId', $args) ? $args['sessionId'] : session_id());
        $lockedByTitle = $args['lockedByTitle'];
        $lockedByIPNo = $args['lockedByIPNo'];

        $this->_pageLockRequireAccess();

        $locks = ModUtil::apiFunc('ZikulaPageLockModule', 'user', 'getLocks', $args);
        if (count($locks) > 0) {
            $lockedBy = '';
            foreach ($locks as $lock) {
                if (strlen($lockedBy) > 0) {
                    $lockedBy .= ', ';
                }
                $lockedBy .= $lock['title'] . " ($lock[ipno]) " . $lock['cdate']->format('Y-m-d H:m:s');
            }
            return array('hasLock' => false, 'lockedBy' => $lockedBy);
        }

        $args['lockedBy'] = null;

        // Look for existing lock
        $query = $this->entityManager->createQueryBuilder()
                                     ->select('count(p.id)')
                                     ->from('ZikulaPageLockModule:PageLockEntity', 'p')
                                     ->where('p.name = :lockName')
                                     ->setParameter('lockName', $lockName)
                                     ->andWhere('p.session = :sessionId')
                                     ->setParameter('sessionId', $sessionId)
                                     ->getQuery();
        $count = (int)$query->getSingleScalarResult();

        $now = time();
        $expireDate = $now + self::PAGELOCKLIFETIME;

        if ($count > 0) {
            // update the existing lock with a new expiry date
            $query = $this->entityManager->createQueryBuilder()
                                         ->update('ZikulaPageLockModule:PageLockEntity', 'p')
                                         ->set('p.edate = :expireDate')
                                         ->where('p.name = :lockName')
                                         ->setParameter('lockName', $lockName)
                                         ->andWhere('p.session = :sessionId')
                                         ->setParameter('sessionId', $sessionId)
                                         ->getQuery();
            $query->getResult();
            $this->entityManager->flush();
        } else {
            // create the new object
            $newLock = new PageLockEntity();
            $newLock->setName($lockName);
            $newLock->setCdate(new \DateTime(DateUtil::getDatetime($now)));
            $newLock->setEdate(new \DateTime(DateUtil::getDatetime($expireDate)));
            $newLock->setSession($sessionId);
            $newLock->setTitle($lockedByTitle);
            $newLock->setIpno($lockedByIPNo);
            // write this back to the db
            $this->entityManager->persist($newLock);
            $this->entityManager->flush();
        }

        $this->_pageLockReleaseAccess();

        return array('hasLock' => true);
    }

    /**
     * Get all the locks for a given page
     *
     * @param string[] $args {
     *      @type string $lockName   The name of the page to return locks for
     *      @type string $sessionId  The ID of the session owning the lock (optional) (default: current session ID)
     *                       }
     *
     * @return array array of locks for $args['lockName']
     */
    public function getLocks($args)
    {
        $lockName = $args['lockName'];
        $sessionId = (array_key_exists('sessionId', $args) ? $args['sessionId'] : session_id());

        $this->_pageLockRequireAccess();

        // remove expired locks
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('ZikulaPageLockModule:PageLockEntity', 'p')
                                     ->where('p.edate < :now')
                                     ->setParameter('now', time())
                                     ->getQuery();
        $query->getResult();

        // get remaining active locks
        $query = $this->entityManager->createQueryBuilder()
                                     ->select('p')
                                     ->from('ZikulaPageLockModule:PageLockEntity', 'p')
                                     ->where('p.name = :lockName')
                                     ->setParameter('lockName', $lockName)
                                     ->andWhere('p.session = :sessionId')
                                     ->setParameter('sessionId', $sessionId)
                                     ->getQuery();
        $locks = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        // now flush to database
        $this->entityManager->flush();

        $this->_pageLockReleaseAccess();

        return $locks;
    }

    /**
     * Releases a lock on a page
     *
     * @param string[] $args {
     *      @type string $lockName   The name of the lock to be released
     *      @type string $sessionId  The ID of the session owning the lock (optional) (default: current session ID)
     *                       }
     *
     * @return bool true
     */
    public function releaseLock($args)
    {
        $lockName = $args['lockName'];
        $sessionId = (array_key_exists('sessionId', $args) ? $args['sessionId'] : session_id());

        $this->_pageLockRequireAccess();

        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('ZikulaPageLockModule:PageLockEntity', 'p')
                                     ->where('p.name = :lockName')
                                     ->setParameter('lockName', $lockName)
                                     ->andWhere('p.session = :sessionId')
                                     ->setParameter('sessionId', $sessionId)
                                     ->getQuery();
        $query->getResult();

        $this->entityManager->flush();

        $this->_pageLockReleaseAccess();

        return true;
    }

    /**
     * Internal locking mechanism to avoid concurrency inside the PageLock functions
     *
     * @return void
     */
    private function _pageLockRequireAccess()
    {
        global $PageLockAccessCount;
        if ($PageLockAccessCount == null) {
            $PageLockAccessCount = 0;
        }

        if ($PageLockAccessCount == 0) {
            global $PageLockFile;
            $ostemp = DataUtil::formatForOS(ServiceUtil::get('service_container')->getParameter('temp_dir'));
            $PageLockFile = fopen($ostemp . '/pagelock.lock', 'w+');
            flock($PageLockFile, LOCK_EX);
            fwrite($PageLockFile, 'This is a locking file for synchronizing access to the PageLock module. Please do not delete.');
            fflush($PageLockFile);
        }

        ++$PageLockAccessCount;
    }

    /**
     * Internal locking mechanism to avoid concurrency inside the PageLock functions
     *
     * @return void
     */
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
