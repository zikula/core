<?php

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
}
