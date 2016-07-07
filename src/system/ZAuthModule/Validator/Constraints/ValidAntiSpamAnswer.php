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
class ValidAntiSpamAnswer extends Constraint
{
    public $message = 'The anti-spam answer is incorrect.';

    public function validatedBy()
    {
        return 'zikula.zauth.antispam_answer.validator';
    }
}
