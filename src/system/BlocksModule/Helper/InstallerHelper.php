<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\BlocksModule\Entity\BlockEntity;

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
        ];
        if (count($filters) <= 1) {
            foreach ($filters as $filter) {
                foreach ($filter as $parameter => $value) {
                    if ($parameter == 'fargs') {
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
                foreach ($filter as $parameter => $value) {
                    // re-map values to array
                    $parameterValues[$legacyFieldMap[$parameter]][] = $value;
                }
            }
            foreach ($parameterValues as $parameter => $valueArray) {
                if ($parameter == 'fargs') {
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

    public function upgradeBkeyToFqClassname(KernelInterface $kernel, BlockEntity $blockEntity)
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
