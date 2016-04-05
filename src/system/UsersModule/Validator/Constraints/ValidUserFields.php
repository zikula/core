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
class ValidUserFields extends Constraint
{
    public $message = 'The fields are invalid.';

    public function validatedBy()
    {
        return 'zikula.user_fields.validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
