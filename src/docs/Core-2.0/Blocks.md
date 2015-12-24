Blocks
======

 - Block classnames should be suffixed by `Block` and located in the `ModuleRoot/Block/` directory.
 - Block classes must implement Zikula\Core\BlockHandlerInterface.
 - Zikula\Core\AbstractBlockHandler is available if desired.
 - Blocks must register their PermissionSchema as part of the owning module's array (in composer.json)
 - The old "info" array of the block has been eliminated.
    - `module` is inferred from providing module.
    - `text_type` is replaced by `getType()`.
    - `allow_multiple` is always `true`.
    - `form_content` is no longer allowed (blocks MUST implement their own content control).
 - The `init` method has been eliminated.
 - `$content` parameter in both the `modify` and `display` method is un-serialized content property from BlockEntity.
 - The `modify` method is expected to handle both the form and processing of the form (like a typical entity controller).
    - The modify method _should_ implement a symfony form handling the data as an array.
    - A simple Twig template is available as a default `ZikulaBlocksModule:Block:default_modify.html.twig`.
        - if you implement your own modify template, **do not** render the `form_start(form)` or `form_end(form)`
          tags within your template.


Block as a Service
------------------

Registering your block class as a service is optional, but can provide greater flexibility with dependencies. If a class
simply extends `AbstractBlockHandler`, it is not required to register the class as a service. Additionally, if a
class implements `BlockHandlerInterface` and requires no construction arguments, registering as a service is
not required.

If you choose to register your block as a service, you must tag your block service with the following tag:

    <tag name="zikula.block" module="<CommonModuleName>" />

The 'CommonModuleName' is the 'camel-cased' bundle name.


Block Filters
-------------

A powerful new filter mechanism has been implemented for blocks. For any block you can set up your own filters based on 
nearly any request attribute or query parameter. These can also be used in any combination. As long as all
filter conditions evaluate to **true** the block will be displayed. Conditions can be compared using any available
comparator: not just `==`, but `!=`, `in_array()` and others. Array values must be a comma-delimited string.