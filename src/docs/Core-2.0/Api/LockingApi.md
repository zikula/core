LockingApi
==========

classname: \Zikula\PageLockModule\Api\LockingApi

service id="zikula_pagelock_module.api.locking"

This class is used to work with page locks. You can require and release locks and determine information
about currently existing locks.

The class makes the following methods available:

    - addLockingCodeForCurrentPage($lockName, $returnUrl = null, $ignoreEmptyLock = false)
    - requireLock($lockName, $lockedByTitle, $lockedByIPNo, $sessionId = '')
    - getLocks($lockName, $sessionId = '')
    - releaseLock($lockName, $sessionId = '')
