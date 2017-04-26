FilterUtil User Manual
======================

FilterUtil brings a filter system to a module's list view. It's using an extended
pagesetter­-like filter syntax providing a maximum of flexibility.


## General syntax ##

By default the filter string is read out from the argument "filter" in URL.
Several filters can used by arguments "filter1" to "filterN". These filters will be connected by "OR".

A single statement is a set of field name, operator and value connected by `:`.
Thereby the field name is the name from Doctrine model or the pntables definition,
while the operator defines the condition type (equal, lower than, aso.).

- `name:eq:Peter` The name is Peter
- `costs:lt:100` The costs are lower than 100

Several statements can be connected by a comma `,` (AND) or an asterisk `*` (OR).

- `name:eq:Peter,costs:lt:100`
  The name is Peter *and* the costs are below 100.
- `name:eq:Peter*name:eq:Chris`
  The name is Peter *or* Chris

Furthermore you can use brackets to group statements.

- `(name:eq:Peter,costs:lt:100)*(name:eq:Chris,costs:lt:150)`  
  Either the name is Peter _and_ the costs are below 100 __or__ the name is Chris
  _and_ the costs are below 150.


## Filter plugins ##

FilterUtil uses filter plugins for processing the statements.
There is a set of plugins in the core which offers a general behaviour.

### Compare ###

The Compare filter is used if there is no other filter plugin configured for a field.
It offers the following operators.

Operators:

- `eq` is equal
- `ne` is not equal
- `lt` is lower than
- `le` is lower or equal than
- `gt` is greater than
- `ge` is greater or equal than
- `search` is any middle coincidence.  
  "bar" will tag on "foobarthis" but not on "foobar" nor "barfoo".
- `like` is like.  
  The value is scanned case insensitive. Also you can use “%” as placeholder.  
  “%bar” will tag on “FOObar”, “Blubbar” and so on, but not on “BarFoo”.
- `likefirst` is for coincidences at the beggining.  
  “foo” will tag on “fOo”, “FOObar”, “FooBlub” and so on, but not on “AnotherFoo”.
- `null` is Empty or NULL
- `notnull` is Not empty and not NULL

### Category ###

The Category plugin filters a category ID or name.

Operators:

- `eq` Item is assigned to the category
- `ne` Item is not assigned to the category
- `sub` Item is assigned to the category or one of it's subcategories

### Date ###

This plugin allows and extends handling with date fields.
It offers the standard time point scan plus a time period handling.

Extended values:

- Prepend one of the keywords "year", "month", "week", "day", "hour", "min"
  followed by a colon to search for a time period. Examples:
  * `date:eq:year:15.07.2009` => All items with a date in year 2009
  * `date:eq:month:15.07.2009` => All items with a date in july 2009
- Using relative time information according to the GNU [Date Input Formats](http://www.gnu.org/software/tar/manual/html_node/tar_113.html) syntax.
  Examples:
  * `date:eq:today` => All items with date of today
  * `date:ge:­24 hours` => All items with date up from 24 hours ago
  * `date:eq:last year` => All items with date in the last year

Operators

- `eq` is equal
- `ne` is not equal
- `gt` is greater than.  
  For time periods: *End* of the given period. Date:gt:today tags all items with date of tomorrow or after.
- `ge` is greater or equal then.  
  For time periods: *Begin* of the given period. Date:ge:today tags all items with date of today or after.
- `lt` is lower than.  
  For time periods: *Begin* of the given period. Date:lt:today tags all items with date of yesterday or before.
- `le` is lower or equal than.  
  For time periods: *End* of the given period. Date:le:today tags all items with date of today or before.
