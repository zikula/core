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

namespace Zikula\CategoriesModule\Builder;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class EntitySelectionBuilder
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Build an array suitable for a select input.
     */
    public function buildFor(string $moduleName): array
    {
        $data = [];
        if (!$this->kernel->isBundle($moduleName)) {
            return $data;
        }

        $module = $this->kernel->getModule($moduleName);
        $capabilities = $module->getMetaData()->getCapabilities();
        if (!isset($capabilities['categorizable'])) {
            return $data;
        }

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
