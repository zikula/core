<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Helper;

use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class InstallerHelper
{
    public function upgradeFilterArray(array $filters)
    {
        $newFilter = [];
        $legacyFieldMap = [
            'module' => '_zkModule',
            'ftype' => '_zkType',
            'fname' => '_zkFunc',
            'fargs' => 'fargs',
            'type' => '_zkType', // core-1.3.x
            'functions' => '_zkFunc', // core-1.3.x
            'customargs' => 'fargs' // core-1.3.x
        ];
        if (count($filters) <= 1) {
            foreach ($filters as $filter) {
                foreach ($filter as $parameter => $value) {
                    if ('fargs' == $parameter) {
                        parse_str($value, $queryVars);
                        foreach ($queryVars as $queryVarName => $queryVarValue) {
                            $newFilter[] = [$queryVarName, '==', $queryVarValue];
                        }
                    } else {
                        $newFilter[] = [$legacyFieldMap[$parameter], '==', $value];
                    }
                }
            }
        } else {
            $parameterValues = [];
            foreach ($filters as $filter) {
                if (is_array($filter)) {
                    foreach ($filter as $parameter => $value) {
                        // re-map values to array
                        $parameterValues[$legacyFieldMap[$parameter]][] = $value;
                    }
                }
            }
            foreach ($parameterValues as $parameter => $valueArray) {
                if ('fargs' == $parameter) {
                    $queryVarValues = [];
                    foreach ($valueArray as $value) {
                        parse_str($value, $queryVars);
                        foreach ($queryVars as $queryVarName => $queryVarValue) {
                            // re-map values to array
                            $queryVarValues[$queryVarName][] = $queryVarValue;
                        }
                    }
                    foreach ($queryVarValues as $varName => $varValue) {
                        $newFilter[] = [$varName, 'in_array', array_unique($varValue)];
                    }
                } else {
                    $newFilter[] = [$parameter, 'in_array', array_unique($valueArray)];
                }
            }
        }

        return $newFilter;
    }

    public function upgradeBkeyToFqClassname(ZikulaHttpKernelInterface $kernel, BlockEntity $blockEntity)
    {
        $moduleName = $blockEntity->getModule()->getName();
        try {
            $moduleBundle = $kernel->getModule($moduleName);
            $blockClassName = $moduleBundle->getNamespace() . '\Block\\' . ucwords($blockEntity->getBkey());
            $blockClassName = preg_match('/.*Block$/', $blockClassName) ? $blockClassName : $blockClassName . 'Block';
        } catch (\Exception $e) {
            $moduleBundle = null;
            $blockClassName = '\\' . ucwords($moduleName).'\\'.'Block\\'.ucwords($blockEntity->getBkey());
            $blockClassName = preg_match('/.*Block$/', $blockClassName) ? $blockClassName : $blockClassName . 'Block';
            $blockClassNameOld = '\\' . ucwords($moduleName) . '_' . 'Block_' . ucwords($blockEntity->getBkey());
            $blockClassName = class_exists($blockClassName) ? $blockClassName : $blockClassNameOld;
        }

        return "$moduleName:$blockClassName";
    }
}
