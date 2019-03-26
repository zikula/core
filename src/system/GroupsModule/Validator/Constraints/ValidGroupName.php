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

namespace Zikula\GroupsModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidGroupName extends Constraint
{
    public $message = 'The fields are invalid.';

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'excludedGid';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
