<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/' => [[['_route' => 'en__RG__home', '_controller' => 'Zikula\\Bundle\\CoreBundle\\Controller\\MainController::homeAction', '_locale' => 'en'], null, null, null, false, false, 1]],
        '/ajaxinstall' => [[['_route' => 'ajaxinstall', '_controller' => 'Zikula\\Bundle\\CoreInstallerBundle\\Controller\\AjaxInstallController::ajaxAction'], null, null, null, false, false, null]],
        '/ajaxupgrade' => [[['_route' => 'ajaxupgrade', '_controller' => 'Zikula\\Bundle\\CoreInstallerBundle\\Controller\\AjaxUpgradeController::ajaxAction'], null, null, null, false, false, null]],
        '/zauth_migration' => [[['_route' => 'zauth_migration', '_controller' => 'Zikula\\Bundle\\CoreInstallerBundle\\Controller\\MigrationController::migrateAction'], null, null, null, false, false, null]],
        '/editor/index' => [[['_route' => 'en__RG__zikula_workflow_editor_index', '_controller' => 'Zikula\\Bundle\\WorkflowBundle\\Controller\\EditorController::indexAction', '_locale' => 'en'], null, ['GET' => 0], null, false, false, null]],
        '/adminpanel' => [[['_route' => 'en__RG__zikulaadminmodule_admin_index', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminController::indexAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'admin', '_zkFunc' => 'index', '_locale' => 'en'], null, null, null, true, false, null]],
        '/adminpanel/newcategory' => [[['_route' => 'en__RG__zikulaadminmodule_admin_newcat', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminController::newcatAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'admin', '_zkFunc' => 'newcat', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/header' => [[['_route' => 'en__RG__zikulaadminmodule_admin_adminheader', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminController::adminheaderAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'admin', '_zkFunc' => 'adminheader', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/footer' => [[['_route' => 'en__RG__zikulaadminmodule_admin_adminfooter', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminController::adminfooterAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'admin', '_zkFunc' => 'adminfooter', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/admininterface/header' => [[['_route' => 'en__RG__zikulaadminmodule_admininterface_header', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminInterfaceController::headerAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'adminInterface', '_zkFunc' => 'header', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/admininterface/footer' => [[['_route' => 'en__RG__zikulaadminmodule_admininterface_footer', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminInterfaceController::footerAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'adminInterface', '_zkFunc' => 'footer', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/admininterface/breadcrumbs' => [[['_route' => 'en__RG__zikulaadminmodule_admininterface_breadcrumbs', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminInterfaceController::breadcrumbsAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'adminInterface', '_zkFunc' => 'breadcrumbs', '_locale' => 'en'], null, ['GET' => 0], null, false, false, null]],
        '/adminpanel/admininterface/securityanalyzer' => [[['_route' => 'en__RG__zikulaadminmodule_admininterface_securityanalyzer', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminInterfaceController::securityanalyzerAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'adminInterface', '_zkFunc' => 'securityanalyzer', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/admininterface/updatecheck' => [[['_route' => 'en__RG__zikulaadminmodule_admininterface_updatecheck', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminInterfaceController::updatecheckAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'adminInterface', '_zkFunc' => 'updatecheck', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/admininterface/menu' => [[['_route' => 'en__RG__zikulaadminmodule_admininterface_menu', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminInterfaceController::menuAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'adminInterface', '_zkFunc' => 'menu', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/ajax/assigncategory' => [[['_route' => 'en__RG__zikulaadminmodule_ajax_changemodulecategory', '_controller' => 'Zikula\\AdminModule\\Controller\\AjaxController::changeModuleCategoryAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'ajax', '_zkFunc' => 'changeModuleCategory', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/ajax/newcategory' => [[['_route' => 'en__RG__zikulaadminmodule_ajax_addcategory', '_controller' => 'Zikula\\AdminModule\\Controller\\AjaxController::addCategoryAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'ajax', '_zkFunc' => 'addCategory', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/ajax/deletecategory' => [[['_route' => 'en__RG__zikulaadminmodule_ajax_deletecategory', '_controller' => 'Zikula\\AdminModule\\Controller\\AjaxController::deleteCategoryAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'ajax', '_zkFunc' => 'deleteCategory', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/ajax/editcategory' => [[['_route' => 'en__RG__zikulaadminmodule_ajax_editcategory', '_controller' => 'Zikula\\AdminModule\\Controller\\AjaxController::editCategoryAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'ajax', '_zkFunc' => 'editCategory', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/ajax/makedefault' => [[['_route' => 'en__RG__zikulaadminmodule_ajax_defaultcategory', '_controller' => 'Zikula\\AdminModule\\Controller\\AjaxController::defaultCategoryAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'ajax', '_zkFunc' => 'defaultCategory', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/ajax/sortcategories' => [[['_route' => 'en__RG__zikulaadminmodule_ajax_sortcategories', '_controller' => 'Zikula\\AdminModule\\Controller\\AjaxController::sortCategoriesAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'ajax', '_zkFunc' => 'sortCategories', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/ajax/sortmodules' => [[['_route' => 'en__RG__zikulaadminmodule_ajax_sortmodules', '_controller' => 'Zikula\\AdminModule\\Controller\\AjaxController::sortModulesAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'ajax', '_zkFunc' => 'sortModules', '_locale' => 'en'], null, null, null, false, false, null]],
        '/adminpanel/config/config' => [[['_route' => 'en__RG__zikulaadminmodule_config_config', '_controller' => 'Zikula\\AdminModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/blocks/admin/view' => [[['_route' => 'en__RG__zikulablocksmodule_admin_view', '_controller' => 'Zikula\\BlocksModule\\Controller\\AdminController::viewAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'admin', '_zkFunc' => 'view', '_locale' => 'en'], null, null, null, false, false, null]],
        '/blocks/admin/block/new' => [[['_route' => 'en__RG__zikulablocksmodule_block_new', '_controller' => 'Zikula\\BlocksModule\\Controller\\BlockController::newAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'block', '_zkFunc' => 'new', '_locale' => 'en'], null, null, null, false, false, null]],
        '/blocks/admin/block/toggle-active' => [[['_route' => 'en__RG__zikulablocksmodule_block_toggleblock', '_controller' => 'Zikula\\BlocksModule\\Controller\\BlockController::toggleblockAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'block', '_zkFunc' => 'toggleblock', '_locale' => 'en'], null, ['POST' => 0], null, false, false, null]],
        '/blocks/config/config' => [[['_route' => 'en__RG__zikulablocksmodule_config_config', '_controller' => 'Zikula\\BlocksModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/blocks/admin/placement/ajax/changeorder' => [[['_route' => 'zikulablocksmodule_placement_changeblockorder', '_controller' => 'Zikula\\BlocksModule\\Controller\\PlacementController::changeBlockOrderAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'placement', '_zkFunc' => 'changeBlockOrder'], null, ['POST' => 0], null, false, false, null]],
        '/categories/admin/category/move' => [[['_route' => 'en__RG__zikulacategoriesmodule_node_move', '_controller' => 'Zikula\\CategoriesModule\\Controller\\NodeController::moveAction', '_zkBundle' => 'ZikulaCategoriesModule', '_zkModule' => 'ZikulaCategoriesModule', '_zkType' => 'node', '_zkFunc' => 'move', '_locale' => 'en'], null, null, null, false, false, null]],
        '/extensions/config' => [[['_route' => 'en__RG__zikulaextensionsmodule_config_config', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/extensions/extensionsinterface/header' => [[['_route' => 'en__RG__zikulaextensionsmodule_extensionsinterface_header', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ExtensionsInterfaceController::headerAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'extensionsInterface', '_zkFunc' => 'header', '_locale' => 'en'], null, null, null, false, false, null]],
        '/extensions/extensionsinterface/footer' => [[['_route' => 'en__RG__zikulaextensionsmodule_extensionsinterface_footer', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ExtensionsInterfaceController::footerAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'extensionsInterface', '_zkFunc' => 'footer', '_locale' => 'en'], null, null, null, false, false, null]],
        '/extensions/extensionsinterface/help' => [[['_route' => 'en__RG__zikulaextensionsmodule_extensionsinterface_help', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ExtensionsInterfaceController::helpAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'extensionsInterface', '_zkFunc' => 'help', '_locale' => 'en'], null, null, null, false, false, null]],
        '/extensions/extensionsinterface/breadcrumbs' => [[['_route' => 'en__RG__zikulaextensionsmodule_extensionsinterface_breadcrumbs', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ExtensionsInterfaceController::breadcrumbsAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'extensionsInterface', '_zkFunc' => 'breadcrumbs', '_locale' => 'en'], null, ['GET' => 0], null, false, false, null]],
        '/extensions/extensionsinterface/links' => [[['_route' => 'en__RG__zikulaextensionsmodule_extensionsinterface_links', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ExtensionsInterfaceController::linksAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'extensionsInterface', '_zkFunc' => 'links', '_locale' => 'en'], null, null, null, false, false, null]],
        '/groups/config/config' => [[['_route' => 'en__RG__zikulagroupsmodule_config_config', '_controller' => 'Zikula\\GroupsModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/groups/admin/create' => [[['_route' => 'en__RG__zikulagroupsmodule_group_create', '_controller' => 'Zikula\\GroupsModule\\Controller\\GroupController::createAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'group', '_zkFunc' => 'create', '_locale' => 'en'], null, null, null, false, false, null]],
        '/groups/membership/admin/getusersbyfragmentastable' => [[['_route' => 'en__RG__zikulagroupsmodule_membership_getusersbyfragmentastable', '_controller' => 'Zikula\\GroupsModule\\Controller\\MembershipController::getUsersByFragmentAsTableAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'membership', '_zkFunc' => 'getUsersByFragmentAsTable', '_locale' => 'en'], null, ['POST' => 0], null, false, false, null]],
        '/mailer/config/config' => [[['_route' => 'en__RG__zikulamailermodule_config_config', '_controller' => 'Zikula\\MailerModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaMailerModule', '_zkModule' => 'ZikulaMailerModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/mailer/config/test' => [[['_route' => 'en__RG__zikulamailermodule_config_test', '_controller' => 'Zikula\\MailerModule\\Controller\\ConfigController::testAction', '_zkBundle' => 'ZikulaMailerModule', '_zkModule' => 'ZikulaMailerModule', '_zkType' => 'config', '_zkFunc' => 'test', '_locale' => 'en'], null, null, null, false, false, null]],
        '/menu/admin/list' => [[['_route' => 'en__RG__zikulamenumodule_menu_list', '_controller' => 'Zikula\\MenuModule\\Controller\\MenuController::listAction', '_zkBundle' => 'ZikulaMenuModule', '_zkModule' => 'ZikulaMenuModule', '_zkType' => 'menu', '_zkFunc' => 'list', '_locale' => 'en'], null, null, null, false, false, null]],
        '/menu/node/move' => [[['_route' => 'en__RG__zikulamenumodule_node_move', '_controller' => 'Zikula\\MenuModule\\Controller\\NodeController::moveAction', '_zkBundle' => 'ZikulaMenuModule', '_zkModule' => 'ZikulaMenuModule', '_zkType' => 'node', '_zkFunc' => 'move', '_locale' => 'en'], null, null, null, false, false, null]],
        '/permissions/config/config' => [[['_route' => 'en__RG__zikulapermissionsmodule_config_config', '_controller' => 'Zikula\\PermissionsModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaPermissionsModule', '_zkModule' => 'ZikulaPermissionsModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/permissions/list' => [[['_route' => 'en__RG__zikulapermissionsmodule_permission_list', '_controller' => 'Zikula\\PermissionsModule\\Controller\\PermissionController::listAction', '_zkBundle' => 'ZikulaPermissionsModule', '_zkModule' => 'ZikulaPermissionsModule', '_zkType' => 'permission', '_zkFunc' => 'list', '_locale' => 'en'], null, null, null, false, false, null]],
        '/permissions/change-order' => [[['_route' => 'en__RG__zikulapermissionsmodule_permission_changeorder', '_controller' => 'Zikula\\PermissionsModule\\Controller\\PermissionController::changeOrderAction', '_zkBundle' => 'ZikulaPermissionsModule', '_zkModule' => 'ZikulaPermissionsModule', '_zkType' => 'permission', '_zkFunc' => 'changeOrder', '_locale' => 'en'], null, ['POST' => 0], null, false, false, null]],
        '/permissions/test' => [[['_route' => 'en__RG__zikulapermissionsmodule_permission_test', '_controller' => 'Zikula\\PermissionsModule\\Controller\\PermissionController::testAction', '_zkBundle' => 'ZikulaPermissionsModule', '_zkModule' => 'ZikulaPermissionsModule', '_zkType' => 'permission', '_zkFunc' => 'test', '_locale' => 'en'], null, ['POST' => 0], null, false, false, null]],
        '/routes/ajax/updateSortPositions' => [[['_route' => 'en__RG__zikularoutesmodule_ajax_updatesortpositions', '_controller' => 'Zikula\\RoutesModule\\Controller\\AjaxController::updateSortPositionsAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'ajax', '_zkFunc' => 'updateSortPositions', '_locale' => 'en'], null, ['POST' => 0], null, false, false, null]],
        '/routes/config/config' => [[['_route' => 'en__RG__zikularoutesmodule_config_config', '_controller' => 'Zikula\\RoutesModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/routes/admin/routes' => [[['_route' => 'en__RG__zikularoutesmodule_route_adminindex', '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::adminIndexAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'adminIndex', '_locale' => 'en'], null, ['GET' => 0], null, false, false, null]],
        '/routes/routes' => [[['_route' => 'en__RG__zikularoutesmodule_route_index', '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::indexAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'index', '_locale' => 'en'], null, ['GET' => 0], null, false, false, null]],
        '/routes/admin/routes/handleSelectedEntries' => [[['_route' => 'en__RG__zikularoutesmodule_route_adminhandleselectedentries', '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::adminHandleSelectedEntriesAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'adminHandleSelectedEntries', '_locale' => 'en'], null, ['POST' => 0], null, false, false, null]],
        '/routes/routes/handleSelectedEntries' => [[['_route' => 'en__RG__zikularoutesmodule_route_handleselectedentries', '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::handleSelectedEntriesAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'handleSelectedEntries', '_locale' => 'en'], null, ['POST' => 0], null, false, false, null]],
        '/routes/update/reload' => [[['_route' => 'en__RG__zikularoutesmodule_update_reload', '_controller' => 'Zikula\\RoutesModule\\Controller\\UpdateController::reloadAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'update', '_zkFunc' => 'reload', '_locale' => 'en'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/routes/update/renew' => [[['_route' => 'en__RG__zikularoutesmodule_update_renew', '_controller' => 'Zikula\\RoutesModule\\Controller\\UpdateController::renewAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'update', '_zkFunc' => 'renew', '_locale' => 'en'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/search/config/config' => [[['_route' => 'en__RG__zikulasearchmodule_config_config', '_controller' => 'Zikula\\SearchModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaSearchModule', '_zkModule' => 'ZikulaSearchModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/search/recent' => [[['_route' => 'en__RG__zikulasearchmodule_search_recent', '_controller' => 'Zikula\\SearchModule\\Controller\\SearchController::recentAction', '_zkBundle' => 'ZikulaSearchModule', '_zkModule' => 'ZikulaSearchModule', '_zkType' => 'search', '_zkFunc' => 'recent', '_locale' => 'en'], null, null, null, false, false, null]],
        '/search/opensearch' => [[['_route' => 'zikulasearchmodule_search_opensearch', '_controller' => 'Zikula\\SearchModule\\Controller\\SearchController::opensearchAction', '_zkBundle' => 'ZikulaSearchModule', '_zkModule' => 'ZikulaSearchModule', '_zkType' => 'search', '_zkFunc' => 'opensearch'], null, null, null, false, false, null]],
        '/securitycenter/config/config' => [[['_route' => 'en__RG__zikulasecuritycentermodule_config_config', '_controller' => 'Zikula\\SecurityCenterModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaSecurityCenterModule', '_zkModule' => 'ZikulaSecurityCenterModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/securitycenter/config/allowedhtml' => [[['_route' => 'en__RG__zikulasecuritycentermodule_config_allowedhtml', '_controller' => 'Zikula\\SecurityCenterModule\\Controller\\ConfigController::allowedhtmlAction', '_zkBundle' => 'ZikulaSecurityCenterModule', '_zkModule' => 'ZikulaSecurityCenterModule', '_zkType' => 'config', '_zkFunc' => 'allowedhtml', '_locale' => 'en'], null, null, null, false, false, null]],
        '/securitycenter/idslog/view' => [[['_route' => 'en__RG__zikulasecuritycentermodule_idslog_view', '_controller' => 'Zikula\\SecurityCenterModule\\Controller\\IdsLogController::viewAction', '_zkBundle' => 'ZikulaSecurityCenterModule', '_zkModule' => 'ZikulaSecurityCenterModule', '_zkType' => 'idsLog', '_zkFunc' => 'view', '_locale' => 'en'], null, null, null, false, false, null]],
        '/securitycenter/idslog/export' => [[['_route' => 'en__RG__zikulasecuritycentermodule_idslog_export', '_controller' => 'Zikula\\SecurityCenterModule\\Controller\\IdsLogController::exportAction', '_zkBundle' => 'ZikulaSecurityCenterModule', '_zkModule' => 'ZikulaSecurityCenterModule', '_zkType' => 'idsLog', '_zkFunc' => 'export', '_locale' => 'en'], null, null, null, false, false, null]],
        '/securitycenter/idslog/purge' => [[['_route' => 'en__RG__zikulasecuritycentermodule_idslog_purge', '_controller' => 'Zikula\\SecurityCenterModule\\Controller\\IdsLogController::purgeAction', '_zkBundle' => 'ZikulaSecurityCenterModule', '_zkModule' => 'ZikulaSecurityCenterModule', '_zkType' => 'idsLog', '_zkFunc' => 'purge', '_locale' => 'en'], null, null, null, false, false, null]],
        '/securitycenter/idslog/deleteentry' => [[['_route' => 'en__RG__zikulasecuritycentermodule_idslog_deleteentry', '_controller' => 'Zikula\\SecurityCenterModule\\Controller\\IdsLogController::deleteentryAction', '_zkBundle' => 'ZikulaSecurityCenterModule', '_zkModule' => 'ZikulaSecurityCenterModule', '_zkType' => 'idsLog', '_zkFunc' => 'deleteentry', '_locale' => 'en'], null, null, null, false, false, null]],
        '/settings' => [[['_route' => 'en__RG__zikulasettingsmodule_settings_main', '_controller' => 'Zikula\\SettingsModule\\Controller\\SettingsController::mainAction', '_zkBundle' => 'ZikulaSettingsModule', '_zkModule' => 'ZikulaSettingsModule', '_zkType' => 'settings', '_zkFunc' => 'main', '_locale' => 'en'], null, null, null, true, false, null]],
        '/settings/locale' => [[['_route' => 'zikulasettingsmodule_settings_locale', '_controller' => 'Zikula\\SettingsModule\\Controller\\SettingsController::localeAction', '_zkBundle' => 'ZikulaSettingsModule', '_zkModule' => 'ZikulaSettingsModule', '_zkType' => 'settings', '_zkFunc' => 'locale'], null, null, null, false, false, null]],
        '/settings/phpinfo' => [[['_route' => 'en__RG__zikulasettingsmodule_settings_phpinfo', '_controller' => 'Zikula\\SettingsModule\\Controller\\SettingsController::phpinfoAction', '_zkBundle' => 'ZikulaSettingsModule', '_zkModule' => 'ZikulaSettingsModule', '_zkType' => 'settings', '_zkFunc' => 'phpinfo', '_locale' => 'en'], null, null, null, false, false, null]],
        '/settings/toggleeditinplace' => [[['_route' => 'en__RG__zikulasettingsmodule_settings_toggleeditinplace', '_controller' => 'Zikula\\SettingsModule\\Controller\\SettingsController::toggleEditInPlaceAction', '_zkBundle' => 'ZikulaSettingsModule', '_zkModule' => 'ZikulaSettingsModule', '_zkType' => 'settings', '_zkFunc' => 'toggleEditInPlace', '_locale' => 'en'], null, null, null, false, false, null]],
        '/theme/admin/view' => [[['_route' => 'en__RG__zikulathememodule_theme_view', '_controller' => 'Zikula\\ThemeModule\\Controller\\ThemeController::viewAction', '_zkBundle' => 'ZikulaThemeModule', '_zkModule' => 'ZikulaThemeModule', '_zkType' => 'theme', '_zkFunc' => 'view', '_locale' => 'en'], null, ['GET' => 0], null, false, false, null]],
        '/login' => [[['_route' => 'en__RG__zikulausersmodule_access_login', '_controller' => 'Zikula\\UsersModule\\Controller\\AccessController::loginAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'access', '_zkFunc' => 'login', '_locale' => 'en'], null, null, null, false, false, null]],
        '/users/account' => [[['_route' => 'en__RG__zikulausersmodule_account_menu', '_controller' => 'Zikula\\UsersModule\\Controller\\AccountController::menuAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'account', '_zkFunc' => 'menu', '_locale' => 'en'], null, null, null, false, false, null]],
        '/users/account/change-language' => [[['_route' => 'en__RG__zikulausersmodule_account_changelanguage', '_controller' => 'Zikula\\UsersModule\\Controller\\AccountController::changeLanguageAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'account', '_zkFunc' => 'changeLanguage', '_locale' => 'en'], null, null, null, false, false, null]],
        '/users/admin/config' => [[['_route' => 'en__RG__zikulausersmodule_config_config', '_controller' => 'Zikula\\UsersModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/users/admin/config/authentication-methods' => [[['_route' => 'en__RG__zikulausersmodule_config_authenticationmethods', '_controller' => 'Zikula\\UsersModule\\Controller\\ConfigController::authenticationMethodsAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'config', '_zkFunc' => 'authenticationMethods', '_locale' => 'en'], null, null, null, false, false, null]],
        '/users/fileIO/export' => [[['_route' => 'en__RG__zikulausersmodule_fileio_export', '_controller' => 'Zikula\\UsersModule\\Controller\\FileIOController::exportAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'fileIO', '_zkFunc' => 'export', '_locale' => 'en'], null, null, null, false, false, null]],
        '/users/livesearch/getUsers' => [[['_route' => 'en__RG__zikulausersmodule_livesearch_getusers', '_controller' => 'Zikula\\UsersModule\\Controller\\LiveSearchController::getUsersAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'liveSearch', '_zkFunc' => 'getUsers', '_locale' => 'en'], null, ['GET' => 0], null, false, false, null]],
        '/register' => [[['_route' => 'en__RG__zikulausersmodule_registration_register', '_controller' => 'Zikula\\UsersModule\\Controller\\RegistrationController::registerAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'registration', '_zkFunc' => 'register', '_locale' => 'en'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/users/admin/getusersbyfragmentastable' => [[['_route' => 'en__RG__zikulausersmodule_useradministration_getusersbyfragmentastable', '_controller' => 'Zikula\\UsersModule\\Controller\\UserAdministrationController::getUsersByFragmentAsTableAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'userAdministration', '_zkFunc' => 'getUsersByFragmentAsTable', '_locale' => 'en'], null, ['POST' => 0], null, false, false, null]],
        '/users/admin/search' => [[['_route' => 'en__RG__zikulausersmodule_useradministration_search', '_controller' => 'Zikula\\UsersModule\\Controller\\UserAdministrationController::searchAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'userAdministration', '_zkFunc' => 'search', '_locale' => 'en'], null, null, null, false, false, null]],
        '/users/admin/mail' => [[['_route' => 'en__RG__zikulausersmodule_useradministration_mailusers', '_controller' => 'Zikula\\UsersModule\\Controller\\UserAdministrationController::mailUsersAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'userAdministration', '_zkFunc' => 'mailUsers', '_locale' => 'en'], null, null, null, false, false, null]],
        '/zauth/account/lost-user-name' => [[['_route' => 'en__RG__zikulazauthmodule_account_lostusername', '_controller' => 'Zikula\\ZAuthModule\\Controller\\AccountController::lostUserNameAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'account', '_zkFunc' => 'lostUserName', '_locale' => 'en'], null, null, null, false, false, null]],
        '/zauth/account/lost-password' => [[['_route' => 'en__RG__zikulazauthmodule_account_lostpassword', '_controller' => 'Zikula\\ZAuthModule\\Controller\\AccountController::lostPasswordAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'account', '_zkFunc' => 'lostPassword', '_locale' => 'en'], null, null, null, false, false, null]],
        '/zauth/account/lost-password/reset' => [[['_route' => 'en__RG__zikulazauthmodule_account_lostpasswordreset', '_controller' => 'Zikula\\ZAuthModule\\Controller\\AccountController::lostPasswordResetAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'account', '_zkFunc' => 'lostPasswordReset', '_locale' => 'en'], null, null, null, false, false, null]],
        '/zauth/account/change-email' => [[['_route' => 'en__RG__zikulazauthmodule_account_changeemail', '_controller' => 'Zikula\\ZAuthModule\\Controller\\AccountController::changeEmailAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'account', '_zkFunc' => 'changeEmail', '_locale' => 'en'], null, null, null, false, false, null]],
        '/zauth/account/change-password' => [[['_route' => 'en__RG__zikulazauthmodule_account_changepassword', '_controller' => 'Zikula\\ZAuthModule\\Controller\\AccountController::changePasswordAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'account', '_zkFunc' => 'changePassword', '_locale' => 'en'], null, null, null, false, false, null]],
        '/zauth/admin/config' => [[['_route' => 'en__RG__zikulazauthmodule_config_config', '_controller' => 'Zikula\\ZAuthModule\\Controller\\ConfigController::configAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'config', '_zkFunc' => 'config', '_locale' => 'en'], null, null, null, false, false, null]],
        '/zauth/fileIO/import' => [[['_route' => 'en__RG__zikulazauthmodule_fileio_import', '_controller' => 'Zikula\\ZAuthModule\\Controller\\FileIOController::importAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'fileIO', '_zkFunc' => 'import', '_locale' => 'en'], null, null, null, false, false, null]],
        '/zauth/admin/getusersbyfragmentastable' => [[['_route' => 'en__RG__zikulazauthmodule_useradministration_getusersbyfragmentastable', '_controller' => 'Zikula\\ZAuthModule\\Controller\\UserAdministrationController::getUsersByFragmentAsTableAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'userAdministration', '_zkFunc' => 'getUsersByFragmentAsTable', '_locale' => 'en'], null, ['POST' => 0], null, false, false, null]],
        '/zauth/admin/user/create' => [[['_route' => 'en__RG__zikulazauthmodule_useradministration_create', '_controller' => 'Zikula\\ZAuthModule\\Controller\\UserAdministrationController::createAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'userAdministration', '_zkFunc' => 'create', '_locale' => 'en'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/t(?'
                    .'|ranslations(?:/([\\w]+)(?:\\.(js|json))?)?(*:52)'
                    .'|heme/(?'
                        .'|combined_asset/([^/]++)/([^/]++)(*:99)'
                        .'|admin/(?'
                            .'|preview/([^/]++)(*:131)'
                            .'|activate/([^/]++)(*:156)'
                            .'|makedefault/([^/]++)(*:184)'
                            .'|delete/([^/]++)(*:207)'
                            .'|credits/([^/]++)(*:231)'
                            .'|var/([^/]++)(*:251)'
                        .')'
                    .')'
                .')'
                .'|/js/routing(?:\\.(js|json))?(*:289)'
                .'|/me(?'
                    .'|dia/cache/resolve/(?'
                        .'|([A-z0-9_-]*)/rc/([^/]++)/(.+)(*:354)'
                        .'|([A-z0-9_-]*)/(.+)(*:380)'
                    .')'
                    .'|nu/(?'
                        .'|admin/(?'
                            .'|view/([^/]++)(*:417)'
                            .'|edit(?:/([^/]++))?(*:443)'
                            .'|delete/([^/]++)(*:466)'
                        .')'
                        .'|node/contextMenu/([^/]++)/([^/]++)(*:509)'
                    .')'
                .')'
                .'|/hooks/(?'
                    .'|([^/]++)(*:537)'
                    .'|togglestatus(*:557)'
                    .'|changeorder(*:576)'
                .')'
                .'|/install(?'
                    .'|(?:/([^/]++))?(*:610)'
                    .'|doc(?:/([^/]++))?(*:635)'
                .')'
                .'|/u(?'
                    .'|pgrade(?:/([^/]++))?(*:669)'
                    .'|sers/admin/(?'
                        .'|list(?:/([^/]++)(?:/([^/]++)(?:/([^/]++)(?:/([^/]++))?)?)?)?(*:751)'
                        .'|user/modify/([1-9]\\d*)(*:781)'
                        .'|approve/([1-9]\\d*)(?:/([^/]++))?(*:821)'
                        .'|delete(?:/([1-9]\\d*))?(*:851)'
                    .')'
                .')'
                .'|/adminpanel/(?'
                    .'|categor(?'
                        .'|ies(?:/(\\d+))?(*:900)'
                        .'|ymenu(?:/([1-9]\\d*))?(*:929)'
                    .')'
                    .'|modifycategory/([1-9]\\d*)(*:963)'
                    .'|deletecategory/([1-9]\\d*)(*:996)'
                    .'|panel(?:/([1-9]\\d*))?(*:1025)'
                .')'
                .'|/blocks/admin/(?'
                    .'|block/(?'
                        .'|edit(?:/([1-9]\\d*))?(*:1081)'
                        .'|delete/([1-9]\\d*)(*:1107)'
                        .'|view/([1-9]\\d*)(*:1131)'
                    .')'
                    .'|p(?'
                        .'|lacement/edit/([1-9]\\d*)(*:1169)'
                        .'|osition/(?'
                            .'|edit(?:/([1-9]\\d*))?(*:1209)'
                            .'|delete/([1-9]\\d*)(*:1235)'
                        .')'
                    .')'
                .')'
                .'|/categories/(?'
                    .'|admin/category/(?'
                        .'|list(?:/([^/]++))?(*:1298)'
                        .'|contextMenu(?:/([^/]++)(?:/([^/]++))?)?(*:1346)'
                    .')'
                    .'|registry/(?'
                        .'|edit(?:/([1-9]\\d*))?(*:1388)'
                        .'|delete/([1-9]\\d*)(*:1414)'
                    .')'
                .')'
                .'|/extensions/module/(?'
                    .'|list(?:/([^/]++))?(*:1465)'
                    .'|mod(?'
                        .'|ules/(?'
                            .'|activate/([1-9]\\d*)/([^/]++)(*:1516)'
                            .'|deactivate/([1-9]\\d*)/([^/]++)(*:1555)'
                        .')'
                        .'|ify/([1-9]\\d*)(?:/(0|1))?(*:1590)'
                    .')'
                    .'|compatibility/([1-9]\\d*)(*:1624)'
                    .'|install/([1-9]\\d*)/([^/]++)(*:1660)'
                    .'|postinstall(?:/([^/]++))?(*:1694)'
                    .'|u(?'
                        .'|pgrade/([1-9]\\d*)/([^/]++)(*:1733)'
                        .'|ninstall/([1-9]\\d*)/([^/]++)(*:1770)'
                    .')'
                .')'
                .'|/se(?'
                    .'|rvices/([^/]++)(*:1802)'
                    .'|arch(?:/(\\d+))?(*:1826)'
                    .'|curitycenter/config/purifierconfig(?:/([^/]++))?(*:1883)'
                .')'
                .'|/groups/(?'
                    .'|a(?'
                        .'|pplication/(?'
                            .'|admin/(deny|accept)/([1-9]\\d*)(*:1952)'
                            .'|create/([1-9]\\d*)(*:1978)'
                        .')'
                        .'|dmin/(?'
                            .'|list(?:/(\\d+))?(*:2011)'
                            .'|edit/([1-9]\\d*)(*:2035)'
                            .'|remove/(\\d+)(*:2056)'
                        .')'
                    .')'
                    .'|list(?:/(\\d+))?(*:2082)'
                    .'|membership/(?'
                        .'|l(?'
                            .'|ist/([1-9]\\d*)(?:/([a-zA-Z]|\\*)(?:/(\\d+))?)?(*:2153)'
                            .'|eave/([1-9]\\d*)(*:2177)'
                        .')'
                        .'|admin/(?'
                            .'|list/([1-9]\\d*)(?:/([a-zA-Z]|\\*)(?:/(\\d+))?)?(*:2241)'
                            .'|add/([1-9]\\d*)/([1-9]\\d*)/([^/]++)(*:2284)'
                            .'|remove(?:/([1-9]\\d*)(?:/([1-9]\\d*))?)?(*:2331)'
                        .')'
                        .'|join/([1-9]\\d*)(*:2356)'
                    .')'
                .')'
                .'|/permissions/(?'
                    .'|edit/([^/]++)(*:2396)'
                    .'|delete/([^/]++)(*:2420)'
                .')'
                .'|/routes/(?'
                    .'|admin/route(?'
                        .'|s/view(?:/([^/]++)(?:/(asc|desc|ASC|DESC)(?:/(\\d+)(?:/(\\d+)(?:\\.(html))?)?)?)?)?(*:2535)'
                        .'|(?:/(\\d+)(?:\\.(html))?)?(*:2568)'
                        .'|/edit(?:/(\\d+)(?:\\.(html))?)?(*:2606)'
                    .')'
                    .'|route(?'
                        .'|s/view(?:/([^/]++)(?:/(asc|desc|ASC|DESC)(?:/(\\d+)(?:/(\\d+)(?:\\.(html))?)?)?)?)?(*:2704)'
                        .'|(?:/(\\d+)(?:\\.(html))?)?(*:2737)'
                        .'|/edit(?:/(\\d+)(?:\\.(html))?)?(*:2775)'
                    .')'
                    .'|update/dump(?:/([^/]++))?(*:2810)'
                .')'
                .'|/logout(?:/([^/]++))?(*:2841)'
                .'|/zauth/(?'
                    .'|a(?'
                        .'|ccount/change\\-email\\-confirm(?:/([^/]++))?(*:2907)'
                        .'|dmin/(?'
                            .'|list(?:/([^/]++)(?:/([^/]++)(?:/([^/]++)(?:/([^/]++))?)?)?)?(*:2984)'
                            .'|user/modify/([1-9]\\d*)(*:3015)'
                            .'|verify/([1-9]\\d*)(*:3041)'
                            .'|send\\-(?'
                                .'|confirmation/([1-9]\\d*)(*:3082)'
                                .'|username/([1-9]\\d*)(*:3110)'
                            .')'
                            .'|toggle\\-password\\-change/([1-9]\\d*)(*:3155)'
                        .')'
                    .')'
                    .'|verify\\-registration(?:/([^/]++)(?:/([^/]++))?)?(*:3214)'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        52 => [[['_route' => 'bazinga_jstranslation_js', '_controller' => 'bazinga.jstranslation.controller:getTranslationsAction', 'domain' => 'messages', '_format' => 'js'], ['domain', '_format'], ['GET' => 0], null, false, true, null]],
        99 => [[['_route' => 'zikulathememodule_combinedasset_asset', '_controller' => 'Zikula\\ThemeModule\\Controller\\CombinedAssetController::assetAction', '_zkBundle' => 'ZikulaThemeModule', '_zkModule' => 'ZikulaThemeModule', '_zkType' => 'combinedAsset', '_zkFunc' => 'asset'], ['type', 'key'], null, null, false, true, null]],
        131 => [[['_route' => 'en__RG__zikulathememodule_theme_preview', '_controller' => 'Zikula\\ThemeModule\\Controller\\ThemeController::previewAction', '_zkBundle' => 'ZikulaThemeModule', '_zkModule' => 'ZikulaThemeModule', '_zkType' => 'theme', '_zkFunc' => 'preview', '_locale' => 'en'], ['themeName'], null, null, false, true, null]],
        156 => [[['_route' => 'en__RG__zikulathememodule_theme_activate', '_controller' => 'Zikula\\ThemeModule\\Controller\\ThemeController::activateAction', '_zkBundle' => 'ZikulaThemeModule', '_zkModule' => 'ZikulaThemeModule', '_zkType' => 'theme', '_zkFunc' => 'activate', '_locale' => 'en'], ['themeName'], null, null, false, true, null]],
        184 => [[['_route' => 'en__RG__zikulathememodule_theme_setasdefault', '_controller' => 'Zikula\\ThemeModule\\Controller\\ThemeController::setAsDefaultAction', '_zkBundle' => 'ZikulaThemeModule', '_zkModule' => 'ZikulaThemeModule', '_zkType' => 'theme', '_zkFunc' => 'setAsDefault', '_locale' => 'en'], ['themeName'], null, null, false, true, null]],
        207 => [[['_route' => 'en__RG__zikulathememodule_theme_delete', '_controller' => 'Zikula\\ThemeModule\\Controller\\ThemeController::deleteAction', '_zkBundle' => 'ZikulaThemeModule', '_zkModule' => 'ZikulaThemeModule', '_zkType' => 'theme', '_zkFunc' => 'delete', '_locale' => 'en'], ['themeName'], null, null, false, true, null]],
        231 => [[['_route' => 'en__RG__zikulathememodule_theme_credits', '_controller' => 'Zikula\\ThemeModule\\Controller\\ThemeController::creditsAction', '_zkBundle' => 'ZikulaThemeModule', '_zkModule' => 'ZikulaThemeModule', '_zkType' => 'theme', '_zkFunc' => 'credits', '_locale' => 'en'], ['themeName'], ['GET' => 0], null, false, true, null]],
        251 => [[['_route' => 'en__RG__zikulathememodule_var_var', '_controller' => 'Zikula\\ThemeModule\\Controller\\VarController::varAction', '_zkBundle' => 'ZikulaThemeModule', '_zkModule' => 'ZikulaThemeModule', '_zkType' => 'var', '_zkFunc' => 'var', '_locale' => 'en'], ['themeName'], null, null, false, true, null]],
        289 => [[['_route' => 'en__RG__fos_js_routing_js', '_controller' => 'fos_js_routing.controller::indexAction', '_format' => 'js', '_locale' => 'en'], ['_format'], ['GET' => 0], null, false, true, null]],
        354 => [[['_route' => 'en__RG__liip_imagine_filter_runtime', '_controller' => 'Liip\\ImagineBundle\\Controller\\ImagineController::filterRuntimeAction', '_locale' => 'en'], ['filter', 'hash', 'path'], ['GET' => 0], null, false, true, null]],
        380 => [[['_route' => 'en__RG__liip_imagine_filter', '_controller' => 'Liip\\ImagineBundle\\Controller\\ImagineController::filterAction', '_locale' => 'en'], ['filter', 'path'], ['GET' => 0], null, false, true, null]],
        417 => [[['_route' => 'en__RG__zikulamenumodule_menu_view', '_controller' => 'Zikula\\MenuModule\\Controller\\MenuController::viewAction', '_zkBundle' => 'ZikulaMenuModule', '_zkModule' => 'ZikulaMenuModule', '_zkType' => 'menu', '_zkFunc' => 'view', '_locale' => 'en'], ['id'], null, null, false, true, null]],
        443 => [[['_route' => 'en__RG__zikulamenumodule_menu_edit', 'id' => null, '_controller' => 'Zikula\\MenuModule\\Controller\\MenuController::editAction', '_zkBundle' => 'ZikulaMenuModule', '_zkModule' => 'ZikulaMenuModule', '_zkType' => 'menu', '_zkFunc' => 'edit', '_locale' => 'en'], ['id'], null, null, false, true, null]],
        466 => [[['_route' => 'en__RG__zikulamenumodule_menu_delete', '_controller' => 'Zikula\\MenuModule\\Controller\\MenuController::deleteAction', '_zkBundle' => 'ZikulaMenuModule', '_zkModule' => 'ZikulaMenuModule', '_zkType' => 'menu', '_zkFunc' => 'delete', '_locale' => 'en'], ['id'], null, null, false, true, null]],
        509 => [[['_route' => 'zikulamenumodule_node_contextmenu', 'action' => 'edit', '_controller' => 'Zikula\\MenuModule\\Controller\\NodeController::contextMenuAction', '_zkBundle' => 'ZikulaMenuModule', '_zkModule' => 'ZikulaMenuModule', '_zkType' => 'node', '_zkFunc' => 'contextMenu'], ['action', 'id'], null, null, false, true, null]],
        537 => [[['_route' => 'en__RG__zikula_hook_hook_edit', '_controller' => 'Zikula\\Bundle\\HookBundle\\Controller\\HookController::editAction', '_locale' => 'en'], ['moduleName'], ['GET' => 0], null, false, true, null]],
        557 => [[['_route' => 'en__RG__zikula_hook_hook_togglesubscribeareastatus', '_controller' => 'Zikula\\Bundle\\HookBundle\\Controller\\HookController::toggleSubscribeAreaStatusAction', '_locale' => 'en'], [], ['POST' => 0], null, false, false, null]],
        576 => [[['_route' => 'en__RG__zikula_hook_hook_changeproviderareaorder', '_controller' => 'Zikula\\Bundle\\HookBundle\\Controller\\HookController::changeProviderAreaOrderAction', '_locale' => 'en'], [], ['POST' => 0], null, false, false, null]],
        610 => [[['_route' => 'en__RG__install', '_controller' => 'Zikula\\Bundle\\CoreInstallerBundle\\Controller\\InstallerController::installAction', 'stage' => 'null', '_locale' => 'en'], ['stage'], null, null, false, true, null]],
        635 => [[['_route' => 'en__RG__doc', '_controller' => 'Zikula\\Bundle\\CoreInstallerBundle\\Controller\\DocController::displayAction', 'name' => 'INSTALL-2.0.md', '_locale' => 'en'], ['name'], null, null, false, true, 2]],
        669 => [[['_route' => 'en__RG__upgrade', '_controller' => 'Zikula\\Bundle\\CoreInstallerBundle\\Controller\\UpgraderController::upgradeAction', 'stage' => 'null', '_locale' => 'en'], ['stage'], null, null, false, true, null]],
        751 => [[['_route' => 'en__RG__zikulausersmodule_useradministration_list', 'sort' => 'uid', 'sortdir' => 'DESC', 'letter' => 'all', 'startnum' => 0, '_controller' => 'Zikula\\UsersModule\\Controller\\UserAdministrationController::listAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'userAdministration', '_zkFunc' => 'list', '_locale' => 'en'], ['sort', 'sortdir', 'letter', 'startnum'], null, null, false, true, null]],
        781 => [[['_route' => 'en__RG__zikulausersmodule_useradministration_modify', '_controller' => 'Zikula\\UsersModule\\Controller\\UserAdministrationController::modifyAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'userAdministration', '_zkFunc' => 'modify', '_locale' => 'en'], ['user'], null, null, false, true, null]],
        821 => [[['_route' => 'en__RG__zikulausersmodule_useradministration_approve', 'force' => false, '_controller' => 'Zikula\\UsersModule\\Controller\\UserAdministrationController::approveAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'userAdministration', '_zkFunc' => 'approve', '_locale' => 'en'], ['user', 'force'], null, null, false, true, null]],
        851 => [[['_route' => 'en__RG__zikulausersmodule_useradministration_delete', 'user' => null, '_controller' => 'Zikula\\UsersModule\\Controller\\UserAdministrationController::deleteAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'userAdministration', '_zkFunc' => 'delete', '_locale' => 'en'], ['user'], null, null, false, true, null]],
        900 => [[['_route' => 'en__RG__zikulaadminmodule_admin_view', 'startnum' => 0, '_controller' => 'Zikula\\AdminModule\\Controller\\AdminController::viewAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'admin', '_zkFunc' => 'view', '_locale' => 'en'], ['startnum'], ['GET' => 0], null, false, true, null]],
        929 => [[['_route' => 'en__RG__zikulaadminmodule_admin_categorymenu', 'acid' => null, '_controller' => 'Zikula\\AdminModule\\Controller\\AdminController::categorymenuAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'admin', '_zkFunc' => 'categorymenu', '_locale' => 'en'], ['acid'], ['GET' => 0], null, false, true, null]],
        963 => [[['_route' => 'en__RG__zikulaadminmodule_admin_modify', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminController::modifyAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'admin', '_zkFunc' => 'modify', '_locale' => 'en'], ['cid'], null, null, false, true, null]],
        996 => [[['_route' => 'en__RG__zikulaadminmodule_admin_delete', '_controller' => 'Zikula\\AdminModule\\Controller\\AdminController::deleteAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'admin', '_zkFunc' => 'delete', '_locale' => 'en'], ['cid'], null, null, false, true, null]],
        1025 => [[['_route' => 'en__RG__zikulaadminmodule_admin_adminpanel', 'acid' => null, '_controller' => 'Zikula\\AdminModule\\Controller\\AdminController::adminpanelAction', '_zkBundle' => 'ZikulaAdminModule', '_zkModule' => 'ZikulaAdminModule', '_zkType' => 'admin', '_zkFunc' => 'adminpanel', '_locale' => 'en'], ['acid'], ['GET' => 0], null, false, true, null]],
        1081 => [[['_route' => 'en__RG__zikulablocksmodule_block_edit', 'blockEntity' => null, '_controller' => 'Zikula\\BlocksModule\\Controller\\BlockController::editAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'block', '_zkFunc' => 'edit', '_locale' => 'en'], ['blockEntity'], null, null, false, true, null]],
        1107 => [[['_route' => 'en__RG__zikulablocksmodule_block_delete', '_controller' => 'Zikula\\BlocksModule\\Controller\\BlockController::deleteAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'block', '_zkFunc' => 'delete', '_locale' => 'en'], ['bid'], null, null, false, true, null]],
        1131 => [[['_route' => 'en__RG__zikulablocksmodule_block_view', '_controller' => 'Zikula\\BlocksModule\\Controller\\BlockController::viewAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'block', '_zkFunc' => 'view', '_locale' => 'en'], ['bid'], null, null, false, true, null]],
        1169 => [[['_route' => 'en__RG__zikulablocksmodule_placement_edit', '_controller' => 'Zikula\\BlocksModule\\Controller\\PlacementController::editAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'placement', '_zkFunc' => 'edit', '_locale' => 'en'], ['pid'], null, null, false, true, null]],
        1209 => [[['_route' => 'en__RG__zikulablocksmodule_position_edit', 'positionEntity' => null, '_controller' => 'Zikula\\BlocksModule\\Controller\\PositionController::editAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'position', '_zkFunc' => 'edit', '_locale' => 'en'], ['positionEntity'], null, null, false, true, null]],
        1235 => [[['_route' => 'en__RG__zikulablocksmodule_position_delete', '_controller' => 'Zikula\\BlocksModule\\Controller\\PositionController::deleteAction', '_zkBundle' => 'ZikulaBlocksModule', '_zkModule' => 'ZikulaBlocksModule', '_zkType' => 'position', '_zkFunc' => 'delete', '_locale' => 'en'], ['pid'], null, null, false, true, null]],
        1298 => [[['_route' => 'en__RG__zikulacategoriesmodule_category_list', 'id' => 1, '_controller' => 'Zikula\\CategoriesModule\\Controller\\CategoryController::listAction', '_zkBundle' => 'ZikulaCategoriesModule', '_zkModule' => 'ZikulaCategoriesModule', '_zkType' => 'category', '_zkFunc' => 'list', '_locale' => 'en'], ['id'], null, null, false, true, null]],
        1346 => [[['_route' => 'en__RG__zikulacategoriesmodule_node_contextmenu', 'id' => null, 'action' => 'edit', '_controller' => 'Zikula\\CategoriesModule\\Controller\\NodeController::contextMenuAction', '_zkBundle' => 'ZikulaCategoriesModule', '_zkModule' => 'ZikulaCategoriesModule', '_zkType' => 'node', '_zkFunc' => 'contextMenu', '_locale' => 'en'], ['action', 'id'], null, null, false, true, null]],
        1388 => [[['_route' => 'en__RG__zikulacategoriesmodule_registry_edit', 'id' => null, '_controller' => 'Zikula\\CategoriesModule\\Controller\\RegistryController::editAction', '_zkBundle' => 'ZikulaCategoriesModule', '_zkModule' => 'ZikulaCategoriesModule', '_zkType' => 'registry', '_zkFunc' => 'edit', '_locale' => 'en'], ['id'], null, null, false, true, null]],
        1414 => [[['_route' => 'en__RG__zikulacategoriesmodule_registry_delete', '_controller' => 'Zikula\\CategoriesModule\\Controller\\RegistryController::deleteAction', '_zkBundle' => 'ZikulaCategoriesModule', '_zkModule' => 'ZikulaCategoriesModule', '_zkType' => 'registry', '_zkFunc' => 'delete', '_locale' => 'en'], ['id'], null, null, false, true, null]],
        1465 => [[['_route' => 'en__RG__zikulaextensionsmodule_module_viewmodulelist', 'pos' => 1, '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ModuleController::viewModuleListAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'module', '_zkFunc' => 'viewModuleList', '_locale' => 'en'], ['pos'], null, null, false, true, null]],
        1516 => [[['_route' => 'en__RG__zikulaextensionsmodule_module_activate', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ModuleController::activateAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'module', '_zkFunc' => 'activate', '_locale' => 'en'], ['id', 'token'], ['GET' => 0], null, false, true, null]],
        1555 => [[['_route' => 'en__RG__zikulaextensionsmodule_module_deactivate', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ModuleController::deactivateAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'module', '_zkFunc' => 'deactivate', '_locale' => 'en'], ['id', 'token'], ['GET' => 0], null, false, true, null]],
        1590 => [[['_route' => 'en__RG__zikulaextensionsmodule_module_modify', 'forceDefaults' => false, '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ModuleController::modifyAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'module', '_zkFunc' => 'modify', '_locale' => 'en'], ['id', 'forceDefaults'], null, null, false, true, null]],
        1624 => [[['_route' => 'en__RG__zikulaextensionsmodule_module_compatibility', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ModuleController::compatibilityAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'module', '_zkFunc' => 'compatibility', '_locale' => 'en'], ['id'], ['GET' => 0], null, false, true, null]],
        1660 => [[['_route' => 'en__RG__zikulaextensionsmodule_module_install', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ModuleController::installAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'module', '_zkFunc' => 'install', '_locale' => 'en'], ['id', 'token'], null, null, false, true, null]],
        1694 => [[['_route' => 'en__RG__zikulaextensionsmodule_module_postinstall', 'extensions' => null, '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ModuleController::postInstallAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'module', '_zkFunc' => 'postInstall', '_locale' => 'en'], ['extensions'], ['GET' => 0], null, false, true, null]],
        1733 => [[['_route' => 'en__RG__zikulaextensionsmodule_module_upgrade', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ModuleController::upgradeAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'module', '_zkFunc' => 'upgrade', '_locale' => 'en'], ['id', 'token'], null, null, false, true, null]],
        1770 => [[['_route' => 'en__RG__zikulaextensionsmodule_module_uninstall', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ModuleController::uninstallAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'module', '_zkFunc' => 'uninstall', '_locale' => 'en'], ['id', 'token'], null, null, false, true, null]],
        1802 => [[['_route' => 'en__RG__zikulaextensionsmodule_services_moduleservices', '_controller' => 'Zikula\\ExtensionsModule\\Controller\\ServicesController::moduleServicesAction', '_zkBundle' => 'ZikulaExtensionsModule', '_zkModule' => 'ZikulaExtensionsModule', '_zkType' => 'services', '_zkFunc' => 'moduleServices', '_locale' => 'en'], ['moduleName'], ['GET' => 0], null, false, true, null]],
        1826 => [[['_route' => 'en__RG__zikulasearchmodule_search_execute', 'page' => -1, '_controller' => 'Zikula\\SearchModule\\Controller\\SearchController::executeAction', '_zkBundle' => 'ZikulaSearchModule', '_zkModule' => 'ZikulaSearchModule', '_zkType' => 'search', '_zkFunc' => 'execute', '_locale' => 'en'], ['page'], null, null, false, true, null]],
        1883 => [[['_route' => 'en__RG__zikulasecuritycentermodule_config_purifierconfig', 'reset' => null, '_controller' => 'Zikula\\SecurityCenterModule\\Controller\\ConfigController::purifierconfigAction', '_zkBundle' => 'ZikulaSecurityCenterModule', '_zkModule' => 'ZikulaSecurityCenterModule', '_zkType' => 'config', '_zkFunc' => 'purifierconfig', '_locale' => 'en'], ['reset'], null, null, false, true, null]],
        1952 => [[['_route' => 'en__RG__zikulagroupsmodule_application_admin', '_controller' => 'Zikula\\GroupsModule\\Controller\\ApplicationController::adminAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'application', '_zkFunc' => 'admin', '_locale' => 'en'], ['action', 'app_id'], null, null, false, true, null]],
        1978 => [[['_route' => 'en__RG__zikulagroupsmodule_application_create', '_controller' => 'Zikula\\GroupsModule\\Controller\\ApplicationController::createAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'application', '_zkFunc' => 'create', '_locale' => 'en'], ['gid'], null, null, false, true, null]],
        2011 => [[['_route' => 'en__RG__zikulagroupsmodule_group_adminlist', 'startnum' => 0, '_controller' => 'Zikula\\GroupsModule\\Controller\\GroupController::adminListAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'group', '_zkFunc' => 'adminList', '_locale' => 'en'], ['startnum'], ['GET' => 0], null, false, true, null]],
        2035 => [[['_route' => 'en__RG__zikulagroupsmodule_group_edit', '_controller' => 'Zikula\\GroupsModule\\Controller\\GroupController::editAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'group', '_zkFunc' => 'edit', '_locale' => 'en'], ['gid'], null, null, false, true, null]],
        2056 => [[['_route' => 'en__RG__zikulagroupsmodule_group_remove', '_controller' => 'Zikula\\GroupsModule\\Controller\\GroupController::removeAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'group', '_zkFunc' => 'remove', '_locale' => 'en'], ['gid'], null, null, false, true, null]],
        2082 => [[['_route' => 'en__RG__zikulagroupsmodule_group_list', 'startnum' => 0, '_controller' => 'Zikula\\GroupsModule\\Controller\\GroupController::listAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'group', '_zkFunc' => 'list', '_locale' => 'en'], ['startnum'], ['GET' => 0], null, false, true, null]],
        2153 => [[['_route' => 'en__RG__zikulagroupsmodule_membership_list', 'letter' => '*', 'startNum' => 0, '_controller' => 'Zikula\\GroupsModule\\Controller\\MembershipController::listAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'membership', '_zkFunc' => 'list', '_locale' => 'en'], ['gid', 'letter', 'startNum'], ['GET' => 0], null, false, true, null]],
        2177 => [[['_route' => 'en__RG__zikulagroupsmodule_membership_leave', '_controller' => 'Zikula\\GroupsModule\\Controller\\MembershipController::leaveAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'membership', '_zkFunc' => 'leave', '_locale' => 'en'], ['gid'], null, null, false, true, null]],
        2241 => [[['_route' => 'en__RG__zikulagroupsmodule_membership_adminlist', 'letter' => '*', 'startNum' => 0, '_controller' => 'Zikula\\GroupsModule\\Controller\\MembershipController::adminListAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'membership', '_zkFunc' => 'adminList', '_locale' => 'en'], ['gid', 'letter', 'startNum'], ['GET' => 0], null, false, true, null]],
        2284 => [[['_route' => 'en__RG__zikulagroupsmodule_membership_add', '_controller' => 'Zikula\\GroupsModule\\Controller\\MembershipController::addAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'membership', '_zkFunc' => 'add', '_locale' => 'en'], ['uid', 'gid', 'token'], null, null, false, true, null]],
        2331 => [[['_route' => 'en__RG__zikulagroupsmodule_membership_remove', 'gid' => 0, 'uid' => 0, '_controller' => 'Zikula\\GroupsModule\\Controller\\MembershipController::removeAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'membership', '_zkFunc' => 'remove', '_locale' => 'en'], ['gid', 'uid'], null, null, false, true, null]],
        2356 => [[['_route' => 'en__RG__zikulagroupsmodule_membership_join', '_controller' => 'Zikula\\GroupsModule\\Controller\\MembershipController::joinAction', '_zkBundle' => 'ZikulaGroupsModule', '_zkModule' => 'ZikulaGroupsModule', '_zkType' => 'membership', '_zkFunc' => 'join', '_locale' => 'en'], ['gid'], null, null, false, true, null]],
        2396 => [[['_route' => 'en__RG__zikulapermissionsmodule_permission_edit', '_controller' => 'Zikula\\PermissionsModule\\Controller\\PermissionController::editAction', '_zkBundle' => 'ZikulaPermissionsModule', '_zkModule' => 'ZikulaPermissionsModule', '_zkType' => 'permission', '_zkFunc' => 'edit', '_locale' => 'en'], ['pid'], null, null, false, true, null]],
        2420 => [[['_route' => 'en__RG__zikulapermissionsmodule_permission_delete', '_controller' => 'Zikula\\PermissionsModule\\Controller\\PermissionController::deleteAction', '_zkBundle' => 'ZikulaPermissionsModule', '_zkModule' => 'ZikulaPermissionsModule', '_zkType' => 'permission', '_zkFunc' => 'delete', '_locale' => 'en'], ['pid'], ['POST' => 0], null, false, true, null]],
        2535 => [[['_route' => 'en__RG__zikularoutesmodule_route_adminview', 'sort' => '', 'sortdir' => 'asc', 'pos' => 1, 'num' => 10, '_format' => 'html', '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::adminViewAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'adminView', '_locale' => 'en'], ['sort', 'sortdir', 'pos', 'num', '_format'], ['GET' => 0], null, false, true, null]],
        2568 => [[['_route' => 'en__RG__zikularoutesmodule_route_admindisplay', '_format' => 'html', 'id' => 0, '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::adminDisplayAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'adminDisplay', '_locale' => 'en'], ['id', '_format'], ['GET' => 0], null, false, true, null]],
        2606 => [[['_route' => 'en__RG__zikularoutesmodule_route_adminedit', 'id' => '0', '_format' => 'html', '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::adminEditAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'adminEdit', '_locale' => 'en'], ['id', '_format'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        2704 => [[['_route' => 'en__RG__zikularoutesmodule_route_view', 'sort' => '', 'sortdir' => 'asc', 'pos' => 1, 'num' => 10, '_format' => 'html', '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::viewAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'view', '_locale' => 'en'], ['sort', 'sortdir', 'pos', 'num', '_format'], ['GET' => 0], null, false, true, null]],
        2737 => [[['_route' => 'en__RG__zikularoutesmodule_route_display', '_format' => 'html', 'id' => 0, '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::displayAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'display', '_locale' => 'en'], ['id', '_format'], ['GET' => 0], null, false, true, null]],
        2775 => [[['_route' => 'en__RG__zikularoutesmodule_route_edit', 'id' => '0', '_format' => 'html', '_controller' => 'Zikula\\RoutesModule\\Controller\\RouteController::editAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'route', '_zkFunc' => 'edit', '_locale' => 'en'], ['id', '_format'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        2810 => [[['_route' => 'en__RG__zikularoutesmodule_update_dumpjsroutes', 'lang' => null, '_controller' => 'Zikula\\RoutesModule\\Controller\\UpdateController::dumpJsRoutesAction', '_zkBundle' => 'ZikulaRoutesModule', '_zkModule' => 'ZikulaRoutesModule', '_zkType' => 'update', '_zkFunc' => 'dumpJsRoutes', '_locale' => 'en'], ['lang'], ['GET' => 0], null, false, true, null]],
        2841 => [[['_route' => 'en__RG__zikulausersmodule_access_logout', 'returnUrl' => null, '_controller' => 'Zikula\\UsersModule\\Controller\\AccessController::logoutAction', '_zkBundle' => 'ZikulaUsersModule', '_zkModule' => 'ZikulaUsersModule', '_zkType' => 'access', '_zkFunc' => 'logout', '_locale' => 'en'], ['returnUrl'], null, null, false, true, null]],
        2907 => [[['_route' => 'en__RG__zikulazauthmodule_account_confirmchangedemail', 'code' => null, '_controller' => 'Zikula\\ZAuthModule\\Controller\\AccountController::confirmChangedEmailAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'account', '_zkFunc' => 'confirmChangedEmail', '_locale' => 'en'], ['code'], null, null, false, true, null]],
        2984 => [[['_route' => 'en__RG__zikulazauthmodule_useradministration_list', 'sort' => 'uid', 'sortdir' => 'DESC', 'letter' => 'all', 'startnum' => 0, '_controller' => 'Zikula\\ZAuthModule\\Controller\\UserAdministrationController::listAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'userAdministration', '_zkFunc' => 'list', '_locale' => 'en'], ['sort', 'sortdir', 'letter', 'startnum'], null, null, false, true, null]],
        3015 => [[['_route' => 'en__RG__zikulazauthmodule_useradministration_modify', '_controller' => 'Zikula\\ZAuthModule\\Controller\\UserAdministrationController::modifyAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'userAdministration', '_zkFunc' => 'modify', '_locale' => 'en'], ['mapping'], null, null, false, true, null]],
        3041 => [[['_route' => 'en__RG__zikulazauthmodule_useradministration_verify', '_controller' => 'Zikula\\ZAuthModule\\Controller\\UserAdministrationController::verifyAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'userAdministration', '_zkFunc' => 'verify', '_locale' => 'en'], ['mapping'], null, null, false, true, null]],
        3082 => [[['_route' => 'en__RG__zikulazauthmodule_useradministration_sendconfirmation', '_controller' => 'Zikula\\ZAuthModule\\Controller\\UserAdministrationController::sendConfirmationAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'userAdministration', '_zkFunc' => 'sendConfirmation', '_locale' => 'en'], ['mapping'], null, null, false, true, null]],
        3110 => [[['_route' => 'en__RG__zikulazauthmodule_useradministration_sendusername', '_controller' => 'Zikula\\ZAuthModule\\Controller\\UserAdministrationController::sendUserNameAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'userAdministration', '_zkFunc' => 'sendUserName', '_locale' => 'en'], ['mapping'], null, null, false, true, null]],
        3155 => [[['_route' => 'en__RG__zikulazauthmodule_useradministration_togglepasswordchange', '_controller' => 'Zikula\\ZAuthModule\\Controller\\UserAdministrationController::togglePasswordChangeAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'userAdministration', '_zkFunc' => 'togglePasswordChange', '_locale' => 'en'], ['user'], null, null, false, true, null]],
        3214 => [
            [['_route' => 'en__RG__zikulazauthmodule_registration_verify', 'uname' => null, 'verifycode' => null, '_controller' => 'Zikula\\ZAuthModule\\Controller\\RegistrationController::verifyAction', '_zkBundle' => 'ZikulaZAuthModule', '_zkModule' => 'ZikulaZAuthModule', '_zkType' => 'registration', '_zkFunc' => 'verify', '_locale' => 'en'], ['uname', 'verifycode'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    static function ($condition, $context, $request) { // $checkCondition
        switch ($condition) {
            case 1: return (($request == null) || ($request->query->get("module") == ""));
            case 2: return preg_match("/[^/]+.md/", $request->query->get("name"));
        }
    },
];
