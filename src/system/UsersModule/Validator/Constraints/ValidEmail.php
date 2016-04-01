<?php

namespace Zikula\UsersModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidEmail extends Constraint
{
    public $message = 'The email "%string%" is invalid.';
    public $excludedUid;

    public function validatedBy()
    {
        return 'zikula.email.validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'excludedUid';
    }
}
