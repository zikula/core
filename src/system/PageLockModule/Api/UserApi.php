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

/**
 * API functions used by user controllers
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * Add the page locking code to the page header
     *
     * @param mixed[] $args {
     *      @type string $lockName        The name of the lock to be released
     *      @type string $returnUrl       The URL to return control to (optional) (default: null)
     *      @type bool   $ignoreEmptyLock Ignore an empty lock name (optional) (default: false)
     * }
     *
     * @return bool true
     */
    public function pageLock($args)
    {
        @trigger_error('The pagelock user api is deprecated. please use the new locking api instead.', E_USER_DEPRECATED);

        $locking = $this->getContainer()->get('zikula_pagelock_module.api.locking');

        $apiArgs = $args;
        if (!isset($apiArgs['returnUrl'])) {
            $apiArgs['returnUrl'] = null;
        }
        if (!isset($apiArgs['ignoreEmptyLock'])) {
            $apiArgs['ignoreEmptyLock'] = false;
        }

        return $locking->addLock($apiArgs['lockName'], $apiArgs['returnUrl'], $apiArgs['ignoreEmptyLock']);
    }

    /**
     * Generate a lock on a page
     *
     * @param string[] $args {
     *      @type string $lockName  The name of the page to create/update a lock on
     *      @type string $sessionId The ID of the session owning the lock (optional) (default: current session ID
     * }
     *
     * @return array('haslock' => true if this user has a lock, false otherwise,
     *                'lockedBy' => if 'haslock' is false then the user who has the lock, null otherwise)
     */
    public function requireLock($args)
    {
        @trigger_error('The pagelock user api is deprecated. please use the new locking api instead.', E_USER_DEPRECATED);

        $locking = $this->getContainer()->get('zikula_pagelock_module.api.locking');

        $apiArgs = $args;
        if (!isset($apiArgs['lockedByTitle'])) {
            $apiArgs['lockedByTitle'] = $this->getContainer()->get('zikula_users_module.current_user')->get('uname');
        }
        if (!isset($apiArgs['lockedByIPNo'])) {
            $apiArgs['lockedByIPNo'] = $this->getContainer()->get('request_stack')->getCurrentRequest()->getClientIp();
        }
        if (!isset($apiArgs['sessionId'])) {
            $apiArgs['sessionId'] = '';
        }

        return $locking->requireLock($apiArgs['lockName'], $apiArgs['lockedByTitle'], $apiArgs['lockedByIPNo'], $apiArgs['sessionId']);
    }

    /**
     * Get all the locks for a given page
     *
     * @param string[] $args {
     *      @type string $lockName  The name of the page to return locks for
     *      @type string $sessionId The ID of the session owning the lock (optional) (default: current session ID)
     * }
     *
     * @return array array of locks for $args['lockName']
     */
    public function getLocks($args)
    {
        @trigger_error('The pagelock user api is deprecated. please use the new locking api instead.', E_USER_DEPRECATED);

        $locking = $this->getContainer()->get('zikula_pagelock_module.api.locking');

        $apiArgs = $args;
        if (!isset($apiArgs['sessionId'])) {
            $apiArgs['sessionId'] = '';
        }

        return $locking->getLocks($apiArgs['lockName'], $apiArgs['sessionId']);
    }

    /**
     * Releases a lock on a page
     *
     * @param string[] $args {
     *      @type string $lockName  The name of the lock to be released
     *      @type string $sessionId The ID of the session owning the lock (optional) (default: current session ID)
     * }
     *
     * @return bool true
     */
    public function releaseLock($args)
    {
        @trigger_error('The pagelock user api is deprecated. please use the new locking api instead.', E_USER_DEPRECATED);

        $locking = $this->getContainer()->get('zikula_pagelock_module.api.locking');

        $apiArgs = $args;
        if (!isset($apiArgs['sessionId'])) {
            $apiArgs['sessionId'] = '';
        }

        return $locking->releaseLock($apiArgs['lockName'], $apiArgs['sessionId']);
    }
}
