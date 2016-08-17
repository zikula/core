<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AuthenticateAdminLogin extends Constraint
{
    public function validatedBy()
    {
        return 'zikula.core_installer.authenticate_admin_login.validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
