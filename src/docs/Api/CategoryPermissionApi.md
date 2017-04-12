CategoryPermissionApi
=====================

classname: \Zikula\CategoriesModule\Api\CategoryPermissionApi

service id = "zikula_categories_module.api.category_permission"

The CategoryPermissionApi helps with implementing permission checks based on categories.

The class makes the following methods available:

    /**
     * Check for access to a certain set of categories.
     *
     * For each category property in the list, check if we have access to that category in that property.
     * Check is done as "ZikulaCategoriesModule:PropertyId:CategoryId", "$regId::$catId"
     *
     * @param AbstractCategoryAssignment[] $categoryAssignments
     * @param int $permLevel
     * @param bool $requireAccessForAll
     * @return bool True if access is allowed to at least one of the categories
     */
    public function hasCategoryAccess(array $categoryAssignments, $permLevel = ACCESS_OVERVIEW, $requireAccessForAll = false);

`$categoryAssignments` must be an array of \Zikula\CategoriesModule\Entity\AbstractCategoryAssignment

use:

    $hasAccess = $this->categoryPermissionApi->hasCategoryAccess($page->getCategoryAssignments());
