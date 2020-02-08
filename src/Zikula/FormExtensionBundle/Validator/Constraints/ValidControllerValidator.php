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

namespace Zikula\Bundle\FormExtensionBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidControllerValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (false === mb_strpos($value, '::')) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }

        [$fqcn, $method] = explode('::', $value);
        if (!class_exists($fqcn) || !is_callable([$fqcn, $method])) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
