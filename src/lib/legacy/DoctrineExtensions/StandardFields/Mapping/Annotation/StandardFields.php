<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineExtensions\StandardFields\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * StandardFields annotation for StandardFields behavioral extension
 *
 * @Annotation
 */
final class StandardFields extends Annotation
{
    public $type;
    public $on = 'update';
    public $field;
    public $value;
}
