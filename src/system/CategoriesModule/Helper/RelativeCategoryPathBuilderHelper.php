<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Helper;

use Zikula\CategoriesModule\Entity\CategoryEntity;

/**
 * Relative category path building helper functions for the categories module.
 */
class RelativeCategoryPathBuilderHelper
{
    /**
     * Given a category with its parent category return an (idenically indexed) array of category-paths
     * based on the given field (name or id make sense).
     *
     * @param CategoryEntity|array $rootCategory The root/parent category
     * @param array                &$cat         The category to process
     * @param boolean              $includeRoot  If true, the root portion of the path is preserved
     *
     * @return The resulting folder path array (which is also altered in place)
     */
    public function buildRelativePathsForCategory($rootCategory, &$cat, $includeRoot = false)
    {
        if (!$rootCategory) {
            return false;
        }

        // remove the root category name of the paths
        // because multilanguage names has different lengths
        $pos = strpos($rootCategory['path'], '/', 1);
        $rootCategory['path'] = substr($rootCategory['path'], $pos);

        $pos = strpos($cat['path'], '/', 1);
        $normalizedPath = substr($cat['path'], $pos);

        // process normalised paths
        $ppos = strrpos($rootCategory['path'], '/') + 1;
        $ipos = strrpos($rootCategory['ipath'], '/') + 1;

        $cat['path_relative'] = substr($normalizedPath, $ppos);
        if (isset($cat['ipath'])) {
            $cat['ipath_relative'] = substr($cat['ipath'], $ipos);
        }

        if (!$includeRoot) {
            $offSlashPath = strpos($cat['path_relative'], '/');
            if (isset($cat['ipath'])) {
                $offSlashIPath = strpos($cat['ipath_relative'], '/');
            }

            if ($offSlashPath !== false) {
                $cat['path_relative'] = substr($cat['path_relative'], $offSlashPath + 1);
            }
            if (isset($cat['ipath']) && $offSlashIPath !== false) {
                $cat['ipath_relative'] = substr($cat['ipath_relative'], $offSlashIPath + 1);
            }
        }

        return $cat;
    }
}
