LinkContainer
=============

The LinkContainer system is a method to store links that the extension can utilize in various User Interfaces.
In conjunction with the smarty plugin `{modulelinks}` @todo replace with Twig plugin

The LinkContainer class must be a registered Symfony service (see below) that provides a LinkContainer
back to the core. The class can actually be named anything and located anywhere as long as the proper FQ path is
provided in `services.xml`. The class must implement `\Zikula\Core\LinkContainer\LinkContainerInterface`.

```xml
    <services>
        <service id="zikulaspecmodule.link_container" class="Zikula\SpecModule\Container\LinkContainer" lazy="true">
            <argument type="service" id="translator" />
            <argument type="service" id="router" />
            <tag name="zikula.link_container" />
        </service>
    </services>
```

See the LinkContainerInterface for more information.