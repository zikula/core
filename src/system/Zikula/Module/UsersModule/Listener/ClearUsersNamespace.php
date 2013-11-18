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

/**
 * Persistent event listener used to clean up the Users module session variables related to logging in.
 *
 * @deprecated since 1.3.7 Use @see UsersModule\Listener\ClearUsersNamespaceListener instead
 */
class Users_Listener_ClearUsersNamespace extends UsersModule\Listener\ClearUsersNamespaceListener
{
}
