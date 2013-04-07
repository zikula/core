<?php

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

