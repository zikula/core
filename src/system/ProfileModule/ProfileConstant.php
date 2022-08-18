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

namespace Zikula\ProfileModule;

class ProfileConstant
{
    /**
     * Event called when creating the choices array for profile properties
     * Subject is instance of \Zikula\ProfileModule\FormTypesChoices
     */
    public const GET_FORM_TYPES_EVENT = 'profile_get_form_types';

    /**
     * The form data prefix
     */
    public const FORM_BLOCK_PREFIX = 'zikulaprofilemodule_editprofile';

    /**
     * The name of the attribute to obtain a realname. requires prefix + ':'
     */
    public const ATTRIBUTE_NAME_DISPLAY_NAME = 'realname';

    /**
     * Module variable key for the avatar image path.
     */
    public const MODVAR_AVATAR_IMAGE_PATH = 'avatarpath';

    /**
     * Default value for the avatar image path.
     */
    public const DEFAULT_AVATAR_IMAGE_PATH = 'public/uploads/avatar';

    /**
     * Module variable key for the flag indicating whether gravatars are allowed or not.
     */
    public const MODVAR_GRAVATARS_ENABLED = 'allowgravatars';

    /**
     * Default value for the flag indicating whether gravatars are allowed or not.
     */
    public const DEFAULT_GRAVATARS_ENABLED = true;

    /**
     * Module variable key for the file name containing the generic gravatar image.
     */
    public const MODVAR_GRAVATAR_IMAGE = 'gravatarimage';

    /**
     * Default value for the file name containing the generic gravatar image.
     */
    public const DEFAULT_GRAVATAR_IMAGE = 'gravatar.jpg';
}
