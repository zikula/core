---
currentMenu: categories
---
# Categories

## About side-wide categories

The Zikula Core contains a central category management system that allows you to implement a wide range of solutions. The most common use of categories is certainly a thematic assignment of content. For example, news articles can be assigned to different topics. The categories module provides a centralized management of site-wide categories which can be arbitrarily used by any extensions.

## General benefits

First of all, the category system offers some functions that are generally useful.

- Categories are managed in a large tree. So they can be subdivided and structured according to any criteria.
- In contrast to a fixed drop-down list, categories can be easily extended by the site operator. Additional categories can be created at any time, which then automatically appear and can be selected for all modules that use the corresponding categories.
- Categories can be translated: Name and description can be stored in several languages directly when editing a category.
- A category can have attributes: these are dynamic properties that can be specified in addition to the master data. For example, you could assign a color to each category, or the name of a responsible person, or any other information.

## Category registries

The most powerful function, however, which really brings out the advantages mentioned above, is "Category Registries". These are entry points that determine where in the category tree an extension wants to use categories for a particular entity.

Several extensions can use the same subtrees or different ones. For example, content pages and news articles could either use the same subject areas (politics, business, sports, etc.) or completely different categories could be used for the two areas. Each extension can define whether only one or more of the categories from the subtree is allowed.

If several extensions use the same categories, it is possible to link relevant content. One could show suitable downloads or videos under an article. Or you could display information from a knowledge base for the current product group in a webshop.

The site operator can freely set the entry points of the registries. And above all, additional registries can be defined. An entity can also use several registries at the same time and thus get several category fields. So if the selection of a topic for a news article is not sufficient, but the selection of a department should also be possible, this can be realized via a second entry point, which refers to a subtree, under which categories for the different departments are located.

This means that you can have any number of drop-down elements for a specific entity, which get their content from the categories. And all this content is dynamically extendable, translatable, can be provided with attributes and can be reused in other extensions.

Finally, it is also possible to filter content based on access rights to the categories, if supported by the corresponding extension. There are two variants: either the content is allowed for which access rights exist to all categories of all registries, or the content is allowed for which at least one authorization exists.

## For developers

- [AbstractCategoryAssignment](Dev/AbstractCategoryAssignment.md)
- [CategoriesType](Dev/CategoriesType.md)
- [CategoryTreeType](Dev/CategoryTreeType.md)
- [CategoryPermissionApi](Dev/CategoryPermissionApi.md)
