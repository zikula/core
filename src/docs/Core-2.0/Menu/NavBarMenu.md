Bootstrap NavBar Menus
======================

1. Standard NavBar (list-style) Menu
2. Drop Menu

Standard NavBar (list-style) Menu
---------------------------------

A normal menu as created in the "DynamicMenus" document can be easily converted to a list-style
NavBar menu by editing the *menu root* and adding one option: "childrenAttributes" and give it
a value of `{"class":"nav navbar-nav"}` (include the braces `{}` and use double-qutoes.)

Then, in the BlocksModule create a (or move the existing) block to the 'navbar' position
(hopefully obviously, your theme needs to support this - the core's Bootstrap theme does so).

Your menu should now work as expected in the navbar.


Drop Menu
---------

This menu is slightly less intuitive. You must create a structure like so:

    Menu Root ("MyDropMenu")
       |
       |
       ––– Displayed "Parent" (first child) ("Click Here")
               |
               -- Child 1 (link)
               -- Child 2 (link)
               -- etc...

As above for the standard NavBar, you must set the `childrenAttributes` as 
`{"class":"nav navbar-nav"}` on the Menu root.

On the displayed parent (the actual first child)
you must set an option for `attributes` with a value of *at least* `{"dropdown":true}`. You can
add an icon or other attributes as you need like so: `{"icon":"fa fa-list","dropdown":true}`

For each grand-child, no special options are required. options like *route* or *URI* and
*attributes* are still functional.

**PLEASE NOTE:** Bootstrap *does not support* multi-level menus (great-grand-children, etc)
and therefore neither does this method in Zikula. While there may be methods to implement
something to this effect, it is currently not implemented here.
