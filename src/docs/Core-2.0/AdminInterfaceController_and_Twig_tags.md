AdminInterfaceController
=============================

ZikulaAdminModule provide functionality that is available to use by other modules and core.
This functionality main concern is to provide standarized look for common module parts like header, footer, links and help in administration.


## Available functions ##

All functions are stored in AdminInterfaceController class.
All functions are templated (Resources/views/AdminInterface)

```headerAction()``` - displays admin header

```footerAction()``` - displays admin footer
 
```breadcrumbsAction()``` - displays admin breadcrumbs 

```menuAction($mode, $template)``` - displays admin menu you can chose beetween two modes ('modules' or 'categories') and two templates ('panel' or 'tabs') 

```developernoticesAction()``` - displays developer notices

```securityanalyzerAction()``` - displays security analyzer

```updatecheckAction()``` - displays update check


## Available Twig tags ##

Module provides Twig tags for all available functions.


```{{ adminHeader() }}```

```{{ adminFooter() }}```

```{{ adminBreadcrumbs() }}```

```{{ adminMenu(mode, template) }}```
and ```{{ adminPanelMenu(mode) }} ```
this function is a short-cut to ```{{ adminMenu(mode, 'panel')```

```{{ adminDeveloperNotices() }}```

```{{ adminSecurityAnalyzer() }}```

```{{ adminUpdateCheck() }}```

