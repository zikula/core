ExtensionsInterfaceController
=============================

ZikulaExtensionsModule provide functionality that is available to use by other modules and core.
This functionality main concern is to provide standarized look for common module parts like header, footer, links and help.


## Available functions ##

All functions are stored in ExtensionsInterfaceController class.
All functions are templated (Resources/views/ExtensionsInterface)

```headerAction($type = 'user', $title = '', $titlelink = '', $setpagetitle = false, $insertflashes = false, $menufirst = false, $image = false)``` - display module header
- type       Type of header (defaults to 'user')
- title      Title to display in header (optional, defaults to module name)
- titlelink  Link to attach to title (optional, defaults to none)
- setpagetitle If set to true, {pagesetvar} is used to set page title
- insertflashes If set to true, {{ showFlashes() }} is put in front of template
- menufirst  If set to true, menu is first, then title
- image   If set to true, module image is also displayed next to title


```footerAction()``` - display module footer

```breadcrumbsAction()``` - display module breadcrumbs

```helpAction($type)``` - display module help type ('user', 'admin') - not functioning  

```linksAction($type)``` - display module links menu

- type Links type admin or user
- links Array with menulinks (text, url, title, id, class, disabled) (optional)
- modname Module name to display links for (optional)
- menuid ID for the unordered list (optional)
- menuclass Class for the unordered list (optional)
- itemclass Class for li element of unordered list
- first Class for the first element (optional)
- last Class for the last element (optional)

## Available Twig tags ##

Module provides Twig tags for all available functions.


```{{ moduleHeader($type = 'user', $title = '', $titlelink = '', $setpagetitle = false, $insertflashes = false, $menufirst = false, $image = false) }}```

```{{ moduleFooter() }}```

```{{ moduleBreadcrumbs() }}```

```{{ moduleHelp(type) }}```

```{{ moduleLinks($type = 'user', $links = '', $modname = '', $menuid = '', $menuclass = '', $itemclass = '', $first = '', $last = '') }}```