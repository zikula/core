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

namespace Zikula\UsersBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidUname extends Constraint
{
    public string $message = 'The uname "%string%" is invalid.';

    public int $excludedUid;

    public function getDefaultOption(): ?string
    {
        return 'excludedUid';
    }
}
