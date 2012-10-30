<?php

namespace Gedmo\Uploadable\FilenameGenerator;

/**
 * FilenameGeneratorSha1
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Uploadable.FilenameGenerator
 * @subpackage FilenameGeneratorSha1
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class FilenameGeneratorSha1 implements FilenameGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public static function generate($filename, $extension)
    {
        return sha1(uniqid($filename.$extension, true)).$extension;
    }
}
