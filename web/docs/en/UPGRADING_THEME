UPGRADING THEMES
================
The model example for a theme is the Andreas08 theme which is shipped with this
package.  You should only attempt the following changes in Zikula 1.3.x after
either a new installation or a successful upgrade, see UPGRADING.

Theme Naming
============
The rule for a theme's name is that the folder must begin with a capital letter
and must not contain any underscore, or dash.  The name can only contain letters
and numbers (and cannot start with a number).  The version.php must be updated to
reflect this.

Template delimiters
===================
Please search and replace all the old tags and replace with the new ones

    <!--[ should be replaced with {
    ]--> should be replaced with }

Please note that if you use browserhacks in your theme you should now use the
{browserhack} plugin.  You can see the documentation for this plugin in
lib/view/plugins/block.browserhack.php - an example might look like this:

    {browserhack condition="if IE 6" assign="ieconditional"}
        <link rel="stylesheet" type="text/css" href="{$stylepath}/fluid960gs/ie6.css" media="screen" />
    {/browserhack}
    {pageaddvar name='header' value=$ieconditional}


If you use embedded javascript in any templates and require to use any plugins
or templating inside the `<script></script>` block, please use double brackets,
{{ and }} instead.

Plugin names
============
Remove the prefix from any plugin names.

Page variables
==============
The name of the 'rawtext' page variable (used with {pageaddvar}, {pagesetvar},
etc.) has changed to 'header'. Any use of the 'rawtext' page variable should be
changed to refer to 'header'.

Multi-lingual themes
====================
You must specify the language in the HTML tag.  This should also include the
language direction.  An example is like this for an XHTML theme.

    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" dir="{langdirection}">

Title and Meta-tags
==================
The title and meta-tags are controlled by a different mechanism.  Please use the
following:

    <meta http-equiv="Content-Type" content="text/html; charset={charset}" />
    <title>{pagegetvar name='title'}</title>
    <meta name="description" content="{$metatags.description}" />
    <meta name="keywords" content="{$metatags.keywords}" />

`$metatags.description` and `$metatags.keywords` are both controlled from the General setting
in the administration panel.

System and module variables
===========================
You may now address system configuration variables directly with

    {$modvars.ZConfig.slogan}

And a module like this:

    {$modvars.MyModule.foo}

Block configuration files
=========================
The theme configuration files in `themes/<ThemeName>/config`
Please update these to point to the relative path of the block templates.
This will vastly increase the speed it takes Zikula to find the requested
template when a block is loaded.  An example might look like this:

    [blockpositions]
    left = blocks/block.tpl
    right = blocks/block.tpl
    center = blocks/block.tpl
    topnav = blocks/topnavblock.tpl
    search = blocks/searchblock.tpl

Theme overrides
===============
In order to override a template in a theme it is now necessary to specically
tell Zikula about it.  You can do it by adding a file called 'overrides.yml'
directly in your `themes/<ThemeName>/` folder.  This file is in YAML format.

Simply specify the relative path of the template you want to override on the left
followed by a colon and space, then the relative path to the overriden template.
There is one warning here.  The path and template name specificed in the module,
block or theme must exist in the override.

So if a module calls template.tpl, then you can have `<pathto>/template.tpl`
but if a module calls `user/template.tpl`, then you have to override
with `<pathto>/user/template.tpl`.

An example is kept in the Andreas08 theme shipped with this package and looks like
this:

    ## original/file.tpl: override/file.tpl
    ---
    system/Search/templates/search_block_search.tpl: themes/Andreas08/templates/modules/Search/search_block_search.tpl
