<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidRegistrationVerification extends Constraint
{
    public string $message = 'The fields are invalid.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
