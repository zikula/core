---
currentMenu: themes
---
# Twig render events

The `\Zikula\ThemeModule\ThemeEvents` class provides these events:

```php
/**
 * Occurs immediately before twig theme engine renders a template.
 * subject is \Zikula\ThemeModule\Bridge\Event\TwigPreRenderEvent
 */
public const PRE_RENDER = 'theme.pre_render';

/**
 * Occurs immediately after twig theme engine renders a template.
 * subject is \Zikula\ThemeModule\Bridge\Event\TwigPostRenderEvent
 */
public const POST_RENDER = 'theme.post_render';
```
