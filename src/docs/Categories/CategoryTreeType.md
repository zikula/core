CategoryTreeType
================

classname: \Zikula\CategoriesModule\Form\Type\CategoryTreeType

The CategoriesModule provides a CategoryTreeType form type for ease of use with Symfony Forms.

With this you can add choice fields for selecting one or multiple categories from the overall categories tree.

###Required Options

 - `translator` - the `translator.default` service

###Optional Options

 - all attributes from the `choice` form type
 - `locale` - (string) the locale to be used (default `en`).
 - `recurse` - (boolean) whether or not to recurse (if false, only direct subfolders are retrieved) (default `true`).
 - `relative` - (boolean) whether or not to also generate relative paths (default `true`).
 - `includeRoot` - (boolean) whether to include the root node or not (default `false`).
 - `includeLeaf` - (boolean) whether to include leaf nodes or not (default `false`).
 - `all` - (boolean) whether or not to return all (or only active) categories (default `false`).
