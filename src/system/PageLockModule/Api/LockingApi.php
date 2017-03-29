<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PageLockModule\Api;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Environment;
use Zikula\PageLockModule\Entity\PageLockEntity;
use Zikula\PageLockModule\Entity\Repository\PageLockRepository;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * Class LockingApi.
 *
 * This class provides means for using a locking mechanism.
 * It should be used instead of the old user api.
 */
class LockingApi
{
    /**
     * Amount of required/opened accesses.
     */
    public static $pageLockAccessCount = 0;

    /**
     * Reference to file containing the internal lock.
     */
    public static $pageLockFile;

    /**
     * length of time to lock a page
     */
    const PAGELOCKLIFETIME = 30;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PageLockRepository
     */
    private $repository;

    /**
     * @var CurrentUserApi
     */
    private $currentUserApi;

    /**
     * @var AssetBag
     */
    private $jsAssetBag;

    /**
     * @var AssetBag
     */
    private $cssAssetBag;

    /**
     * @var AssetBag
     */
    private $footerAssetBag;

    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * LockingApi constructor.
     *
     * @param Twig_Environment   $twig           Twig service instance
     * @param RequestStack       $requestStack   RequestStack service instance
     * @param EntityManager      $entityManager  EntityManager service instance
     * @param PageLockRepository $repository     PageLockRepository service instance
     * @param CurrentUserApi     $currentUserApi CurrentUserApi service instance
     * @param AssetBag           $jsAssetBag     AssetBag service instance for JS files
     * @param AssetBag           $cssAssetBag    AssetBag service instance for CSS files
     * @param AssetBag           $footerAssetBag AssetBag service instance for footer code
     * @param Asset              $assetHelper    Asset helper service instance
     * @param string             $tempDir        Directory for temporary files
     */
    public function __construct(
        Twig_Environment $twig,
        RequestStack $requestStack,
        EntityManager $entityManager,
        PageLockRepository $repository,
        CurrentUserApi $currentUserApi,
        AssetBag $jsAssetBag,
        AssetBag $cssAssetBag,
        AssetBag $footerAssetBag,
        Asset $assetHelper,
        $tempDir)
    {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->currentUserApi = $currentUserApi;
        $this->jsAssetBag = $jsAssetBag;
        $this->cssAssetBag = $cssAssetBag;
        $this->footerAssetBag = $footerAssetBag;
        $this->assetHelper = $assetHelper;
        $this->tempDirectory = $tempDir;
    }

    /**
     * Requires a lock and adds the page locking code to the page header
     *
     * @param string $lockName        The name of the lock to be released
     * @param string $returnUrl       The URL to return control to (optional) (default: null)
     * @param bool   $ignoreEmptyLock Ignore an empty lock name (optional) (default: false)
     *
     * @return bool true
     */
    public function addLock($lockName, $returnUrl = null, $ignoreEmptyLock = false)
    {
        if (empty($lockName) && $ignoreEmptyLock) {
            return true;
        }

        $this->jsAssetBag->add($this->assetHelper->resolve('@ZikulaPageLockModule:js/PageLock.js'));
        $this->cssAssetBag->add($this->assetHelper->resolve('@ZikulaPageLockModule:css/style.css'));

        $lockInfo = $this->requireLock($lockName, $this->currentUserApi->get('uname'), $this->requestStack->getCurrentRequest()->getClientIp());

        $hasLock = $lockInfo['hasLock'];
        if ($hasLock) {
            return true;
        }

        // add a good margin to lock timeout when pinging
        $pingTime = (self::PAGELOCKLIFETIME * 2 / 3);

        $templateParameters = [
            'lockedBy' => $lockInfo['lockedBy'],
            'lockName' => $lockName,
            'hasLock' => $hasLock,
            'returnUrl' => $returnUrl,
            'pingTime' => $pingTime
        ];
        $lockedHtml = $this->twig->render('@ZikulaPageLockModule/lockedWindow.html.twig', $templateParameters);

        $this->footerAssetBag->add($lockedHtml);

        return true;
    }

    /**
     * Generate a lock on a page
     *
     * @param string $lockName      The name of the page to create/update a lock on
     * @param string $lockedByTitle Name of user owning the current lock
     * @param string $lockedByIPNo  Ip address of user owning the current lock
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
        $count = $this->repository->getActiveLockAmount($lockName, $theSessionId);

        $expireDate = new \DateTime();
        $expireDate->setTimestamp(time() + self::PAGELOCKLIFETIME);

        if ($count > 0) {
            // update the existing lock with a new expiry date
            $this->repository->updateExpireDate($lockName, $theSessionId, $expireDate);
        } else {
            // create the new object
            $newLock = new PageLockEntity();
            $newLock->setName($lockName);
            $newLock->setCdate(new \DateTime());
            $newLock->setEdate($expireDate);
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
        $this->repository->deleteExpiredLocks();

        // get remaining active locks
        $locks = $this->repository->getActiveLocks($lockName, $theSessionId);

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

        $this->repository->deleteByLockName($lockName, $theSessionId);

        $this->releaseAccess();

        return true;
    }

    /**
     * Internal locking mechanism to avoid concurrency inside the PageLock functions.
     *
     * @return void
     */
    private function requireAccess()
    {
        if (null === self::$pageLockAccessCount) {
            self::$pageLockAccessCount = 0;
        }

        if (self::$pageLockAccessCount == 0) {
            self::$pageLockFile = fopen($this->tempDirectory . '/pagelock.lock', 'w+');
            flock(self::$pageLockFile, LOCK_EX);
            fwrite(self::$pageLockFile, 'This is a locking file for synchronizing access to the PageLock module. Please do not delete.');
            fflush(self::$pageLockFile);
        }

        ++self::$pageLockAccessCount;
    }

    /**
     * Internal locking mechanism to avoid concurrency inside the PageLock functions.
     *
     * @return void
     */
    private function releaseAccess()
    {
        --self::$pageLockAccessCount;

        if (self::$pageLockAccessCount == 0) {
            flock(self::$pageLockFile, LOCK_UN);
            fclose(self::$pageLockFile);
        }
    }
}
