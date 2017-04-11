FilterUtil Developer Manual
===========================

FilterUtil brings a filter system to a module's list view. It's using an extended
pagesetter­-like filter syntax providing a maximum of flexibility, able to work on
pntable and Doctrine based modules.

In this document we will help you understand the implementation of _FilterUtil_
in your module.


## Implement FilterUtil ##

Implementing FilterUtil is very easy. 
First get an instance of Doctrine2 query builder and initialise it with an entity. You can join
other entities if you want to filter on their data.

    [php]
    $queryBuilder = $this->entityManager->createQueryBuilder();
    $queryBuilder->select('alias')
        ->from('entityname', 'alias')
        ->join('entityname.relation', 'relationalias');

Second load FilterUtil and create a new instance by:

    [php]
    $pluginManager = new PluginManager(new Config($queryBuilder), $plugins = [], $restrictions = []);
    $filterUtil = new FilterUtil($pluginManager, $request = null, $filterKey = 'filter');
    
1. **filterKey**: The name of the filter variable in the URL. Default: “filter”
2. **request**: A request object to obtain the filter sting
   only needed if the filter is set via GET or it reads values from GET.
3. **plugins**: Array of instantiated plugin classes to load.
   If no plugin with default = true given the compare plugin is loaded and used for unconfigured fields.
   Multiple objects of the same plugin with different configurations are possible.
4. **restrictions**: Allowed operators per field.
   Array in the form "field's name => operator array". If a field is not set in this array all
   operators are allowed.

Now you can enrich the QueryBuilder by calling the enrichQuery Method and get the Doctrine2
query Object from the QueryBuilder.

    [php]
    $filterUtil->enrichQuery();
    $query = $queryBuilder->getQuery();

### Available plugins configuration ###

#### Compare ####

constructor:
- **$fields**:  Array of fields to use for this plugin. Default: all Fields.
- **$ops**: Array of operators to enable. Default: all available.
- **$default**: Set the plugin as default plugin. Default: false.

available operators:
- eq, ne, lt, le, gt, ge, search, like, likefirst, likelast, null, notnull

#### Category ####

The "Category" uses the Zikula Categorization System to filter the result.
The configuration values are:

constructor:
- **$modname**: modulename of the entity to filter the category registry. 
- **$property**: Array of propertys to filter the category registry.
- **$fields**:  Array of fields to use for this plugin. Default: 'category'
- **$ops**: Array of operators to enable. Default: all available.
- **$default**: Set the plugin as default plugin. Default: false.

available operators:
- eq, ne, sub

The value can be a categoryid or the category name

> **CAUTION**
> The plugin assumes that the filtered table has the category value on
> a Doctrine2 category Entity, so the field must not exist as a column.

#### Date ####

Plugin to filter on date fields

constructor:
- **$fields**:  Array of fields to use for this plugin. Default: all Fields.
- **$ops**: Array of operators to enable. Default: all available.
- **$default**: Set the plugin as default plugin. Default: false.

available operators:
- eq, ne, lt, le, gt, ge

#### ReplaceName ####

This plugin allows you to replace the name of fields.
E.g. *author* -> *cr_uid* or *date* -> *cr_date*.

constructor:
- **pairs**: Array of replace pairs (field name => replace with).


### Methods list ###

    [php]
    $filterUtil->getFilter();

Current filter as FilterUtil filter string getter.

    [php]
    $filterUtil->setFilter($filterString);

Filter string setter using the FilterUtil filter string syntax.
`$filterString` can be a string or an array of strings getting connected by `*` (OR).

    [php]
    $filterUtil->addFilter($filterString);

Adds a filter string or an array of filters.
If filter does not begin with `,` or `*` append it as "AND".

    [php]
    $filterUtil->andFilter($filterString);

Adds a filter string with "AND".

    [php]
    $filterUtil->orFilter($filterString);

Adds a filter string with "OR".

    [php]
    $filterUtil->enrichQuery();

Enrich the QueryBuilder with the filter conditions.


### Adding own plugins to FilterUtil ###

Simply create your own plugin with the following abstract base classes and interfaces
and add an instance to the plugins array.

- **AbstractPlugin** Abstract class for all plugins.
- **BuildInterface** Interface for plugins that reads conditions from the Filter converts them for the QueryBuilder.
- **AbstractBuildPlugin** Implementation of the BuildInterface.
- **JoinInterface** Interface for plugins that add joins to the QueryBuilder.
- **ReplaceInterface** Interface that can replace any field, operator and value of conditions in the filter string.
