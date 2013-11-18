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
 * Persistent event listener for user.login.veto events that forces the change of a user's password.
 *
 * @deprecated since 1.3.7 Use @see UsersModule\Listener\ForcedPasswordChangeListener instead
 */
class Users_Listener_ForcedPasswordChange extends UsersModule\Listener\ForcedPasswordChangeListener
{
}
