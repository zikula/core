<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Yaml;

/**
 * Yaml offers convenience methods to load and dump YAML.
 *
 * @todo remove after migration to Symfony 3.x and use new parameters instead
 * @see https://github.com/zikula/core/issues/2889
 * @see http://symfony.com/blog/new-in-symfony-3-1-customizable-yaml-parsing-and-dumping
 */
class Yaml extends \Symfony\Component\Yaml\Yaml
{
    /**
* @inheritDoc
     */
    public static function dump($array, $inline = 2, $indent = 4, $exceptionOnInvalidType = false, $objectSupport = false)
    {
        if ($indent < 1) {
            throw new \InvalidArgumentException('The indentation must be greater than zero.');
        }

        $yaml = new Dumper();
        $yaml->setIndentation($indent);

        return $yaml->dump($array, $inline, 0, $exceptionOnInvalidType, $objectSupport);
    }
}
