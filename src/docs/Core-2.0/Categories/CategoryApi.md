CategoryApi
===========

classname: \Zikula\CategoriesModule\Api\CategoryApi

service id = "zikula_categories_module.api.category"

The CategoryApi is used to work with categories and their data.

The class makes the following methods available:

- Creating categories:

    - createCategory($rootPath, $name, $value = null, $displayname = null, $description = null, $attributes = null)

- Retrieving single categories:

    - getCategoryById($categoryId)
    - getCategoryByPath($apath, $pathField = 'path')

- Retrieving multiple categories:

    - getCategories($where = '', $sort = '', $assocKey = '', $columnArray = null)
    - getCategoriesByRegistry($registry)
    - getCategoriesByParentId($id, $sort = '', $relative = false, $all = false, $assocKey = '', $attributes = null)
    - getCategoriesByPath($apath, $sort = '', $pathField = 'ipath', $includeLeaf = true, $all = false, $exclPath = '', $assocKey = '', $attributes = null, $columnArray = null)

- Retrieving multiple sub categories:

    - getSubCategories($categoryId, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null, $sortField = 'sort_value', $columnArray = null)
    - getSubCategoriesByPath($apath, $pathField = 'ipath', $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null, $sortField = 'sort_value')
    - getSubCategoriesForCategory($category, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCat = null, $assocKey = '', $attributes = null, $sortField = 'sort_value', $columnArray = null)


- Deleting categories:

    - deleteCategoryById($categoryId)
    - deleteCategoriesByPath($path, $pathField = 'ipath')

Note that there are also other apis and some helper services providing even more functionality. See the other markdown documents in this folder for more information about them.
