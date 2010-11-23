
# FILTERUTIL - DEVELOPERS MANUAL #

FilterUtil brings a filter system to a module's list view. It's using an extended
pagesetter­-like filter syntax providing a maximum of flexibility, able to work on
pntable and Doctrine based modules.

In this document we will help you understand the implementation of _FilterUtil_
in your module.


## Implement FilterUtil ##

Implementing FilterUtil is very easy. Simply load it and create a new instance by:

    [php]
    $filterUtil = new FilterUtil('ModuleName', $table, $config);

Where `$table` can be a *Doctrine_Table* instance or a pntable name string,
and `$config` is an array of additional attributes:

1. **varname**: The name of the filter variable in the URL. Default: “filter”
2. **plugins**: The plugins to load and their configuration.
   A plugin name => plugin configuration array.
3. **alias**: Alias of the main table of the query.
4. **join**: (only for pntables) A reference to a DBUtil JOIN array.
   This will be extended by FilterUtil if necessary.


### Available plugins configuration ###

#### Default ####

The "Default" plugin is used for each field and doesn't need to be configured.

#### Category ####

The "Category" uses the Zikula Categorization System to filter the result.
The configuration values are:

- **fields**:  Array of fields to use for category filter.
- **property**: Property name in the category register. Default: “Main”.
- **ops**: Array of operators to enable (available operators: eq, ne, sub).

> **CAUTION**
> The plugin assumes that the filtered table has the category value on
> the system Categories table, so the field must not exist as a column.

#### Date ####

- **fields**: Array of fields to use for category filter.
- **ops**: Array of operators to enable (available operators: eq, ne, lt, le, gt, ge).

#### Mnlist ####

- **fields**:  This is an array in the form: (name => array(field=>'', table=>'', comparefield=>'')).
  *name* is the filter field name. *field* is the id field in the mn-relationship table.
  *table* is the table of the mn-relationship. *comparefield* is the field to compare with in the table.
- **ops**: Array of operators to enable (available operators: eq, ne).

#### Pmlist ####

- **fields**:  Array of fields to work on.
- **ops**: Array of operators to enable (available operators: eq, ne, lt, le, gt, ge, like, null, notnull).
- **default**: Whether this plugin is the default plugin for all fields or not.

#### ReplaceName ####

This plugin allows you to replace the name of fields.
E.g. *author* -> *cr_uid* or *date* -> *cr_date*.

- **pairs**: Array of replace pairs (field name => replace with).


### Methods list ###

    [php]
    $filterUtil->setVarName($varname);

Setter of the filter variable name in the URL. 

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
    $filterUtil->enrichQuery($query);

Enrich a *Doctrine_Query* with the filter conditions.

    [php]
    $result = $filterUtil->getSQL();

Returns the where clause and join conditions for pntable based modules.
The result is an array with:

- *where*: the WHERE clause.
- *join*: A reference to the DBUtil JOIN array.



### Adding plugins to the list ###

FilterUtil uses an Event to collect the available plugins on the system.
Your module may register a persistent EventHandler on its installer:

    [php]
    EventUtil::registerPersistentModuleHandler(
        'ModuleName',
        'zikula.filterutil.get_plugin_classes',
        array('ModuleName_EventHandler_Listeners', 'getFilterClasses')
    );

to add your plugins to the list with:

    [php]
	class ModuleName_EventHandler_Listeners
	{
		public static function getFilterClasses(Zikula_Event $event)
		{
			$classNames = array();
			$classNames['id1'] = 'ModuleName_Filter_Filter1';
			$classNames['id2'] = 'ModuleName_Filter_Filter2';

			$event->setData(array_merge((array)$event->getData(), $classNames));
		}
	}

and you will be able to filter, for instance, an *author* field with your plugin
with an addition to your configuration array like:

    [php]
	$config['plugins']['id1']['fields'][] = 'author'
