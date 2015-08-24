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

namespace Zikula\UsersModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * User entity class.
 *
 * @todo remove this class at Core-2.0
 *  see notes in \Zikula\UsersModule\Entity\BaseUserEntity
 *  This class is not technically deprecated, but will be returned to a concrete class in Core-2.0
 *
 * @ORM\Entity
 * @ORM\Table(name="users",indexes={@ORM\Index(name="uname",columns={"uname"}), @ORM\Index(name="email",columns={"email"})})
 */
class UserEntity extends BaseUserEntity
{
}
