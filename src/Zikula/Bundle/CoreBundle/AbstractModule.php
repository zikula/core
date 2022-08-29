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

namespace Zikula\Bundle\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\Composer\Scanner;

abstract class AbstractModule extends Bundle
{
    public function getMetaData(): MetaData
    {
        $scanner = new Scanner();
        $jsonPath = $this->getPath() . '/composer.json';
        $jsonContent = $scanner->decode($jsonPath);
        $metaData = new MetaData($jsonContent);
        if (!empty($this->container) && $this->container->has('translator')) {
            $metaData->setTranslator($this->container->get('translator'));
        }

        return $metaData;
    }
}
