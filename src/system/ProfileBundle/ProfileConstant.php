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

namespace Zikula\ProfileBundle;

class ProfileConstant
{
    /**
     * Event called when creating the choices array for profile properties
     * Subject is instance of \Zikula\ProfileBundle\FormTypesChoices
     */
    public const GET_FORM_TYPES_EVENT = 'profile_get_form_types';

    /**
     * The form data prefix
     */
    public const FORM_BLOCK_PREFIX = 'zikulaprofilebundle_editprofile';

    /**
     * The name of the attribute to obtain a realname. requires prefix + ':'
     */
    public const ATTRIBUTE_NAME_DISPLAY_NAME = 'realname';
}
