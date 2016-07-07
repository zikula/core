<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidUname extends Constraint
{
    public $message = 'The uname "%string%" is invalid.';
    public $excludedUid;

    public function validatedBy()
    {
        return 'zikula.users.uname.validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'excludedUid';
    }
}
