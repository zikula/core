<?php

namespace Zikula\UsersModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidUname extends Constraint
{
    public $message = 'The uname "%string%" is invalid.';

    public function validatedBy()
    {
        return 'zikula.uname.validator';
    }
}
