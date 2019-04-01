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

namespace Zikula\UsersModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidEmail extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The email "%string%" is invalid.';

    /**
     * @var int
     */
    public $excludedUid;

    public function getDefaultOption()
    {
        return 'excludedUid';
    }
}
