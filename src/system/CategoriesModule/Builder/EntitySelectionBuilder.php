<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Builder;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class EntitySelectionBuilder
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * EntitySelectionBuilder constructor.
     * @param ZikulaHttpKernelInterface $kernel
     */
    public function __construct(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * build an array suitable for a select input
     * @param $moduleName
     * @return array
     */
    public function buildFor($moduleName)
    {
        if ($this->kernel->isBundle($moduleName)) {
            $module = $this->kernel->getModule($moduleName);
            if (!class_exists($module->getVersionClass())) {
                // this check just confirming a Core-2.0 spec bundle - remove in 2.0.0
                $capabilities = $module->getMetaData()->getCapabilities();
                if (isset($capabilities['categorizable'])) {
                    $data = [];
                    $keys = array_keys($capabilities['categorizable']);
                    $entityList = is_int($keys[0]) ? $capabilities['categorizable'] : $capabilities['categorizable'][$keys[0]];
                    foreach ($entityList as $fullyQualifiedEntityName) {
                        $nameParts = explode('\\', $fullyQualifiedEntityName);
                        $entityName = array_pop($nameParts);
                        $data[$entityName] = $entityName;
                    }

                    return $data;
                }
            }
        }
    }
}
