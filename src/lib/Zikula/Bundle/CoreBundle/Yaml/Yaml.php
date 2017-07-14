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
 */
class Yaml extends \Symfony\Component\Yaml\Yaml
{
    /**
     * {@inheritdoc}
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
