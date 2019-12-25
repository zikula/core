<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidControllerValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        [$fqcn, $method] = explode('::', $value);
        if (!class_exists($fqcn) || !is_callable([$fqcn, $method])) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
