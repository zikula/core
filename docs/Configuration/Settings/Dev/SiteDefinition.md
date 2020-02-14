---
currentMenu: main-settings
---
# Site definition

The Core bundle provides a site definition which can be used for adding additional logic for computing site titles, site meta descriptions as well as logos and icon files.

The basic interface for this is defined in `\Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface` as follows:

```php
interface SiteDefinitionInterface
{
    public function getName(): string;

    public function getSlogan(): string;

    public function getPageTitle(): string;

    public function getMetaDescription(): string;

    public function getLogoPath(): ?string;

    public function getMobileLogoPath(): ?string;

    public function getIconPath(): ?string;
}
```

## How to add custom logic

A default implementation is provided by `\Zikula\Bundle\CoreBundle\Site\SiteDefinition`.

You can subclass this and tell the dependency injection system that it should use your custom subclass whenever the interface is expected.

For this add something like the following to `/config/services_custom.yaml`:

```yaml
services:
    zikula.site_definition: '@Acme\FooTheme\Site\AcmeCustomSiteDefinition'
```
