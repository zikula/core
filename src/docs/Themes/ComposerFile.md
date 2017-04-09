Theme Composer file
===================

Filename: `composer.json`

Status: Required

Description: define the extension and various features and capabilities. Specific keys are required and others are optional.
The composer.json file is also used by composer and packagist to enable package installation.

Please see the [Official JSON Schema](https://getcomposer.org/doc/04-schema.md) for full details on most items

 - name: (required) can be anything, but typically `<vendor>/<name>-<type>`
 - version: (required) must adhere to [semver requirements](http://semver.org).
 - description: (required) a one sentence description of the extension (translatable)
 - type: (required) zikula-theme
 - license: (required) License name (string) or an array of license names (array of strings) under which the extension 
   is provided. You must use the standardized identifier acronym for the license as defined by 
   [Software Package Data Exchange](http://spdx.org/licenses/)
 - authors: (optional but recommended) an array of objects indicating author or group information
 - autoload: (required) object defining psr-4 namespace object
 - require: (required) object defining bundle dependencies
 - extra: (required) the zikula object with required keys
   - zikula: (required)
     - core-compatibility: (required) a [version compatibility string](https://getcomposer.org/doc/01-basic-usage.md#package-versions) defining core compatibility
     - class: (required) the fully qualified name of the Bundle class
     - displayname: (required) the common name for the bundle (translatable)
     - capabilities: (required if controllers are used) an object of objects defining capabilities of the extension
         - user: true|false
         - admin: true|false
         - xhtml: true|false
