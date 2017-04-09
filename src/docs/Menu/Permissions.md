Permissions
===========

The permission schema for **dynamic** menu items is `ZikulaMenuModule::id`.

In order to hide menu items from user groups, you must create a new permission rule like:

    Unregistered    ZikulaMenuModule::id    ::9     No access

you can add multiple ids to the same rule like so:

    Unregistered    ZikulaMenuModule::id    ::(9|11)     No access

The `ID` of any given menu item is available in the MenuModule where the item was created.

All children of the menu item are affected by the same permission rule.

The permission is not based on its placement in a **block** but rather its placement **anywhere**.

The rule checks for access to `ACCESS_READ` level permissions.

As always, the effectiveness of a permission rule is affected by its order in the rules list. Higher placement has 
higher priority. Be sure to place this rule above other rules granting general access to items.
