UserCategoriesApi
=================

classname: \Zikula\CategoriesModule\Api\UserCategoriesApi

service id = "zikula_categories_module.api.user_categories"

The UserCategoriesApi provides means for getting information regarding categories specific for a certain user.

The class makes the following methods available:

    - getUserRootCategory($returnCategory = false, $returnField = 'id')
    - getUserCategories($relative = false)
    - getUserCategoryName($uid = 0)
