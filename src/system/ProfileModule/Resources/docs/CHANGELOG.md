# Changelog

## 3.0.0

- Overhauled property management and translation
- Added new property types (e.g. money, birthday, and more)
- Overhauled avatar management

## 2.1.0

- Adapted to Zikula 1.4.3
- Migrated to Twig
- Migrated to Symfony Forms

## 2.0.0

- Core 1.4.0 Compatibility

## 1.6.0

- Requires at least Zikula core version 1.3.0.
- Implements listeners for hook-like UI events published by the Users module. 
  These event listeners allow Profile to inject the appropriate UI code into views
  and forms in the Users module where the display or editing of Profile information
  is appropriate.
- Module upgraded to Zikula version 1.3.0 standards, including:
  - Templates renamed to use the new standard .tpl suffix.
  - Various API calls changed to 1.3.0 equivalents.
  - Module directory structure changed to comply with 1.3.0 standards.
  - Files renamed to 1.3.0 standards.
  - PNG image file format is used in place of GIF.
  - etc.
- Various bug fixes, including issues #60, #61, #66.

## 1.5.3

- Changed the permission for the view function.
  Changed from `Profile:view:` to the more standard `Profile::view`, and 
  also removed the double permission requirement for the view function 
  (Read access needed only for `Profile::view` not for both that 
  AND `Profile::`). (issue #55)

**NOTE:** If your current permission set includes `Profile:view:`, then
it must be changed to `Profile::view`.

## 1.5.2

## 1.5.1

## 1.5

- 'Mandatory' and 'Core' field types are now deprecated
- New field types list:
  - 2  - Third party (normal) editable and deactivable
  - 1  - Normal (site specific) editable and deactivable
  - 0  - Third party (mandatory) editable and non-deactivable
  - -1 - Third party (hidden) managed by the third party instance
- New criteria:
  - type > 0 are deactivable
  - type >= 0 are editable by the user
- Checkbox fields cannot be required.
- New module configuration to set the fields to show in the registration form
