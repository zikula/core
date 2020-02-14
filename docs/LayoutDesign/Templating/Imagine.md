---
currentMenu: templating
---
# Image manipulation with Imagine

Imagine is implemented in Zikula Core by the installation and pre-configuration of the [LiipImagineBundle](https://github.com/liip/LiipImagineBundle).

Configuration is located at `/config/packages/imagine.yaml`

In order for the developer to create their own filter(s), one must edit this config file directly.

Once this is done, use the provided Twig filter to create the images you require.

```twig
<img src="{{ 'images/flowers.jpg'|imagine_filter('my_thumb') }}" />
<img src="{{ 'images/logo_with_title.png'|imagine_filter('z100x100') }}" />
```

Zikula Core provides a default cache resolver. By default images are cached to `/public/imagine/cache/<filterName>`.

Zikula Core provides a `zikula_root` loader if it is required to load images from locations other than `/public/`.

Use this loader to locate images from the `/public/uploads` directory:

```yaml
# /config/packages/imagine.yaml
filter_sets:
    my_uploads_filter:
        data_loader: zikula_root
        jpeg_quality: 75
        filters:
            thumbnail: { size: [100, 100], mode: inset }
```

```twig
# my template
<img src="{{ 'public/uploads/flowers.jpg'|imagine_filter('my_uploads_filter') }}" />
```

## External resources

- [Imagine docs](https://imagine.readthedocs.io/en/stable/)
- [LiipImagineBundle docs](https://symfony.com/doc/current/bundles/LiipImagineBundle/index.html)
