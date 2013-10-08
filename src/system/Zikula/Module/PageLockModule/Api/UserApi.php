<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\PageLockModule\Api;

/**
 * length of time to lock a page
 *
 */
define('PageLockLifetime', 30);

use UserUtil;
use PageUtil;
use ThemeUtil;
use ModUtil;
use Zikula_View;
use DataUtil;
use DateUtil;
use ServiceUtil;
use Zikula\Module\PageLockModule\Entity\PageLockEntity;

class UserApi extends \Zikula_AbstractApi
{
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

        // Use "PageLockLifetime*2/3" to add a good margin to lock timeout when pinging

        // disabled due to #2556 and #2745
        // $returnUrl = DataUtil::formatForDisplayHTML($returnUrl);

        $html .= "
PageLock.LockName = '$lockName';
PageLock.ReturnUrl = '$returnUrl';
PageLock.PingTime = " . (PageLockLifetime*2/3) . ";
PageLock.LockedHTML = '" . $lockedHtml . "';
 /* ]]> */</script>";

        PageUtil::addVar('header', $html);

        return true;
    }


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

        $entity = 'Zikula\Module\PageLockModule\Entity\PageLockEntity';

        // Look for existing lock
        $query = $this->entityManager->createQuery("SELECT COUNT(p.id) FROM $entity p WHERE p.name = :lockName AND p.session = :sessionId");
        $query->setParameter('lockName', $lockName);
        $query->setParameter('sessionId', $sessionId);
        $count = $query->getSingleScalarResult();

        $now = time();
        $expireDate = $now + PageLockLifetime;

        if ($count > 0) {
            // update the existing lock with a new expiry date
            $dql = "UPDATE $entity p SET p.edate = {$expireDate} WHERE p.name = :lockName AND p.session = :sessionId";
            $query = $this->entityManager->createQuery($dql);
            $query->setParameter('lockName', $lockName);
            $query->setParameter('sessionId', $sessionId);
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


    public function getLocks($args)
    {
        $lockName = $args['lockName'];
        $sessionId = (array_key_exists('sessionId', $args) ? $args['sessionId'] : session_id());

        $this->_pageLockRequireAccess();

        $now = time();

        $entity = 'Zikula\Module\PageLockModule\Entity\PageLockEntity';

        // remove expired locks
        $query = $this->entityManager->createQuery("DELETE FROM $entity p WHERE p.edate < :now");
        $query->setParameter('now', $now);
        $query->getResult();

        // get remaining active locks
        $query = $this->entityManager->createQuery("SELECT p FROM $entity p WHERE p.name = :lockName AND p.session = :sessionId");
        $query->setParameter('lockName', $lockName);
        $query->setParameter('sessionId', $sessionId);
        $locks = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        // now flush to database
        $this->entityManager->flush();

        $this->_pageLockReleaseAccess();

        return $locks;
    }

    public function releaseLock($args)
    {
        $lockName = $args['lockName'];
        $sessionId = (array_key_exists('sessionId', $args) ? $args['sessionId'] : session_id());

        $this->_pageLockRequireAccess();

        $entity = 'Zikula\Module\PageLockModule\Entity\PageLockEntity';
        $query = $this->entityManager->createQuery("DELETE FROM $entity p WHERE p.name = :lockName AND p.session = :sessionId");
        $query->setParameter('lockName', $lockName);
        $query->setParameter('sessionId', $sessionId);
        $query->getResult();
        $this->entityManager->flush();

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
            $ostemp = DataUtil::formatForOS(ServiceUtil::get('service_container')->getParameter('temp_dir'));
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
