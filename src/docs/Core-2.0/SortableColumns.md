Sortable Columns
================

SortableColumns is a zikula component to help manage data table column headings that can be clicked to sort the data.

# Controller:

```php
    use Zikula\Component\SortableColumns\SortableColumns;

    // in controller method
    $orderBy = $request->query->get('orderby', 'pageid');
    $currentSortDirection = $request->query->get('sdir', Column::DIRECTION_DESCENDING);
    
    $sortableColumns = new SortableColumns($this->get('router'), 'zikulapagesmodule_admin_index', 'orderby', 'sdir');
    $sortableColumns->addColumn(new Column('pageid')); // first added is automatically the default
    $sortableColumns->addColumn(new Column('title'));
    $sortableColumns->addColumn(new Column('cr_date'));
    $sortableColumns->setOrderBy($sortableColumns->getColumn($orderBy), $currentSortDirection);
    $sortableColumns->setAdditionalUrlParameters(array(
        'language' => isset($filterData['language']) ? $filterData['language'] : null,
    ));

    $templateParameters['sort'] = $sortableColumns->generateSortableColumns();
```

# twig template:

```twig
    <tr>
        <th><a class='{{ sort.pageid.class }}' href='{{ sort.pageid.url }}'>{{ __('ID') }}</a></th>
        <th><a class='{{ sort.title.class }}' href='{{ sort.title.url }}'>{{ __('Title') }}</a></th>
        <th><a class='{{ sort.cr_date.class }}' href='{{ sort.cr_date.url }}'>{{ __('Created') }}</a></th>
    </tr>
```