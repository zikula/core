CategoryRegistryApi
===================

classname: \Zikula\CategoriesModule\Api\CategoryRegistryApi

service id = "zikula_categories_module.api.category_registry"

The CategoryRegistryApi can be used to create, fetch and delete category registries acting as entry points
for category sub trees used by modules to provide category selections.

The class makes the following methods available:

    - deleteRegistry($modName, $categoryId = null)
    - insertRegistry($modName, $entityName, $property, $categoryId)
    - updateRegistry($registryId, $modName, $entityName, $property, $categoryId)
    - registerModuleCategory($registryData)
    - registerModuleCategories($registryDataArray)
    - getModuleRegistries($modName, $entityName)
    - getModuleRegistriesIds($modName, $entityName)
    - getModuleCategoryIds($modName, $entityName, $arrayKey = 'property')
    - getModuleCategoryId($modName, $entityName, $property, $default = null)
