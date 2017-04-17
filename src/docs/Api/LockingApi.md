LockingApi
==========

This class is no longer a core module.

classname: \Zikula\PageLockModule\Api\LockingApi

service id="zikula_pagelock_module.api.locking"

This class is used to work with page locks. You can require and release locks and determine information
about currently existing locks.

The class makes the following methods available:

    /**
     * Requires a lock and adds the page locking code to the page header
     *
     * @param string $lockName        The name of the lock to be released
     * @param string $returnUrl       The URL to return control to (optional) (default: null)
     * @param bool   $ignoreEmptyLock Ignore an empty lock name (optional) (default: false)
     *
     * @return bool true
     */
    public function addLock($lockName, $returnUrl = null, $ignoreEmptyLock = false);

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
    public function requireLock($lockName, $lockedByTitle, $lockedByIPNo, $sessionId = '');

    /**
     * Get all the locks for a given page
     *
     * @param string $lockName  The name of the page to return locks for
     * @param string $sessionId The ID of the session owning the lock (optional) (default: current session ID)
     *
     * @return array array of locks for $lockName
     */
    public function getLocks($lockName, $sessionId = '');

    /**
     * Releases a lock on a page
     *
     * @param string $lockName  The name of the lock to be released
     * @param string $sessionId The ID of the session owning the lock (optional) (default: current session ID)
     *
     * @return bool true
     */
    public function releaseLock($lockName, $sessionId = '');
