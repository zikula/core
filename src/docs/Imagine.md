# Imagine

Imagine is implemented in Zikula Core by the installation and pre-configuration of the [LiipImagineBundle](https://github.com/liip/LiipImagineBundle).

Documentation: https://symfony.com/doc/current/bundles/LiipImagineBundle/index.html

Configuration is located at `src/app/config/imagine.yml`

In order for the developer to create their own filter(s), one must edit this config file directly.

Once this is done, use the provided Twig filter to create the images you require.

```twig
<img src="{{ 'images/flowers.jpg'|imagine_filter('my_thumb') }}" />
<img src="{{ 'images/logo_with_title.png'|imagine_filter('z100x100') }}" />
```

Zikula Core provides a default cache resolver. By default images are cached to `/web/imagine/cache/<filterName>`.

Zikula Core provides a `zikula_root` loader if it is required to load images from locations other than `/web`.
Use this loader to locate images from the `web/uploads` directory:
```yaml
# src/app/config/imagine.yml
filter_sets:
    my_uploads_filter:
        data_loader: zikula_root
        jpeg_quality: 75
        filters:
            thumbnail: { size: [100, 100], mode: inset }
```

```twig
# my template
<img src="{{ 'web/uploads/flowers.jpg'|imagine_filter('my_uploads_filter') }}" />
```
