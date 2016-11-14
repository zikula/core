Dynamic Menus
=============

A Dynamic Menu is a menu that is created via the User Interface in the MenuModule. There is 
great flexibility with this system, such that menu of any type can be created and placed in your
site anywhere you choose.

Create a menu
-------------

In order to create a menu, you must first create a *menu root*. From the main MenuModule admin
page, select "New Menu" from the menu bar. Choose a unique **title** - this title will never be
displayed but is used to reference your menu within the application. For now, leave the options
blank and we will return to this later. Submit your new root. Your are returned to the menu list
and your new root is listed therein with three action choices: Edit Children, Edit Menu Root
and Delete.

####Edit Children

You will notice that a 'dummy child' has been auto-created for you. This is simply to allow
you to easily add more children via the context menu (right-click on the dummy child to view
the context menu). First *edit* the dummy child to create your first item. Rename it to "Google"
and *add* an **URI** option - enter `http://www.google.com/` as the value. Add another option
and select **attributes**. Set the value to `{"icon": "fa fa-google"}` (include the braces `{}`
and also notice that double-quotes are used).

Note: The value of **attributes** as well as several other options (indicated with an asterisk *)
must be a json_encoded string of option key-value pairs. Other options (indicated by a plus +)
must have a string-boolean e.g. "true" or "false" (without the quotes). This will be converted
to true boolean.

As many options as needed can be added via this interface. Most common will be URI or route.

Now you can add additional menu children by right clicking your *Google* child and selecting
"Add sibling item (after selected)" from the context menu and repeating the process above. Add
at least two more children.

You now have a "standard" menu that you can utilize anywhere on your site via the BlocksModule.

####Create a menu block

Visit the BlocksModule admin interface and select "Create new block" from the admin menu. Select
"MenuModule/Menu" from the choices. Give your block the title "Test Menu" and leave the
description blank. For Menu Name, you must enter the *unique title* you created as your menu
root above. For "options" enter
`{"template": "ZikulaMenuModule:Override:bootstrap_fontawesome.html.twig"}`.
Setting the template as this value allows automatically uses all the bootstrap and fontawesome
goodness that is pre-installed with Zikula. You are allowed to create and utilize your own
templates as needed in your custom situation. More on that later. Select the "Left" or "Right"
Positions for now as appropriate for your theme (navbar modules will be discussed later).

You new menu should be seen in the location you created it!
