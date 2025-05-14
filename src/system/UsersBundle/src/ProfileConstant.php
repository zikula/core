<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersBundle;

class ProfileConstant
{
    /**
     * The name of the attribute to obtain the display name. Requires prefix + ':'
     */
    public const ATTRIBUTE_NAME_DISPLAY_NAME = 'displayName';

    /**
     * The name of the attribute to obtain the first name. Requires prefix + ':'
     */
    public const ATTRIBUTE_NAME_FIRST_NAME = 'firstName';

    /**
     * The name of the attribute to obtain the last name. Requires prefix + ':'
     */
    public const ATTRIBUTE_NAME_LAST_NAME = 'lastName';

    /**
     * The name of the attribute to obtain the avatar. Requires prefix + ':'
     */
    public const ATTRIBUTE_NAME_AVATAR = 'avatar';
}
