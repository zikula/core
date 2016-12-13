Categories helper services
==========================

For some uncommon tasks you may need advanced functionality that is not provided by the category API services. In such situations you can also use additional helper services the core provides, too.

Processing helper
-----------------

classname: \Zikula\CategoriesModule\Helper\CategoryProcessingHelper

service id = "zikula_categories_module.category_processing_helper"

Contains methods used for processing categories for input or output.

The class makes the following methods available:

    - validateCategoryData($data)
    - processCategoryName($name)
    - processCategoryParent($parentId)
    - processCategoryDisplayName($displayName, $name)
    - processCategoryPath($parentPath, $categoryName)
    - processCategoryIPath($parentIpath, $categoryId)
    - processCategoryAttributes($category, $attribNames, $attribValues)
    - mayCategoryBeDeletedOrMoved($category)

Sorting helper
--------------

classname: \Zikula\CategoriesModule\Helper\CategorySortingHelper

service id = "zikula_categories_module.category_sorting_helper"

Contains methods used for performing or amending the sorting of categories.

The class makes the following methods available:

    - compareName($catA, $catB)
    - compareDesc($catA, $catB)
    - sortByLocale(&$cats, $func = 'compareName')
    - resequence($cats, $step = 1)
    - insertTreeLeaf(&$tree, $entry, $currentpath = null)
    - sortTree($tree, &$cats)
    - sortCategories($cats, $sortField = '', $assocKey = '')

Copy and move helper
--------------------

classname: \Zikula\CategoriesModule\Helper\CopyAndMoveHelper

service id = "zikula_categories_module.copy_and_move_helper"

Contains utility methods for copying and moving categories.

The class makes the following methods available:

    - moveCategoriesById($cid, $newParentId)
    - moveCategoriesByPath($apath, $newParentId, $pathField = 'ipath', $includeRoot = true)
    - moveSubCategoriesByPath($apath, $newParentId, $pathField = 'ipath')
    - copyCategoriesById($cid, $newParentId)
    - copyCategoriesByPath($apath, $newParentId, $pathField = 'ipath', $includeRoot = true)
    - copySubCategoriesByPath($apath, $newParentId, $pathField = 'ipath')

Hierarchy helper
----------------

classname: \Zikula\CategoriesModule\Helper\HierarchyHelper

service id = "zikula_categories_module.hierarchy_helper"

Contains methods for querying hierarchy-based relationships between categories.

The class makes the following methods available:

    - isDirectSubCategory($rootCat, $cat)
    - isDirectSubCategoryById($rootId, $categoryId)
    - isSubCategory($rootCat, $cat)
    - isSubCategoryById($rootId, $categoryId)
    - hasDirectSubcategories($cid, $countOnly = false, $all = true)

HTML tree helper
----------------

classname: \Zikula\CategoriesModule\Helper\HtmlTreeHelper

service id = "zikula_categories_module.html_tree_helper"

Contains utility methods used for displaying category tree structures.

The class makes the following methods available:

    - getCategoryTreeStructure($cats)
    - getSelector($cats, $field = 'id', $selectedValue = '0', $name = 'category[parent_id]', $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $submit = false, $displayPath = false, $doReplaceRootCat = true, $multipleSize = 1, $fieldIsAttribute = false, $cssClass = '', $lang = null)

JS tree helper
--------------

classname: \Zikula\CategoriesModule\Helper\JsTreeHelper

service id = "zikula_categories_module.js_tree_helper"

Contains utility methods used for using category tree structures in JavaScript.

The class makes the following methods available:

    - getCategoryTreeJs($cats, $doReplaceRootCat = true, $sortable = false, array $options = [])
    - getCategoryTreeJqueryJs($cats, $doReplaceRootCat = true, $sortable = false, array $options = [])
    - getJsTreeNodeFromCategory(CategoryEntity $category)
    - getJsTreeNodeFromCategoryArray($categories)
    - getCategoryTreeJsNode($category)

Path builder helper
-------------------

classname: \Zikula\CategoriesModule\Helper\PathBuilderHelper

service id = "zikula_categories_module.path_builder_helper"

Contains utility methods used for (re)building category pathes.

The class makes the following methods available:

    - buildRelativePaths($rootCatIDs, &$cats, $includeRoot = false)
    - buildPaths($cats, $field = 'name')
    - rebuildPaths($pathField = 'path', $sourceField = 'name', $leafId = 0)

Relative category path builder helper
-------------------------------------

classname: \Zikula\CategoriesModule\Helper\RelativeCategoryPathBuilderHelper

service id = "zikula_categories_module.relative_category_path_builder_helper"

Contains utility methods used for building relative category pathes.
Used by the path builder helper shown above.

The class makes the following methods available:

    - buildRelativePathsForCategory(CategoryEntity $rootCategory, &$cat, $includeRoot = false)
