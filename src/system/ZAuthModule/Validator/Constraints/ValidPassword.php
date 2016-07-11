<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidPassword extends Constraint
{
    public $message = 'The password "%string%" is invalid.';

    public function validatedBy()
    {
        return 'zikula.zauth.password.validator';
    }
}
