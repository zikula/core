<?php

namespace Gedmo\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Slug annotation for Sluggable behavioral extension
 *
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Mapping.Annotation
 * @subpackage Slug
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class Slug extends Annotation
{
    /** @var array<string> @required */
    public $fields = array();
    /** @var boolean */
    public $updatable = true;
    /** @var string */
    public $style = 'default'; // or "camel"
    /** @var boolean */
    public $unique = true;
    /** @var string */
    public $separator = '-';
    /** @var array<Gedmo\Mapping\Annotation\SlugHandler> */
    public $handlers = array();
}

