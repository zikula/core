Twig Render Events
==================

class: `\Zikula\ThemeModule\ThemeEvents`

    /**
     * Occurs immediately before twig theme engine renders a template.
     * subject is \Zikula\ThemeModule\Bridge\Event\TwigPreRenderEvent
     */
    const PRE_RENDER = 'theme.pre_render';

    /**
     * Occurs immediately after twig theme engine renders a template.
     * subject is \Zikula\ThemeModule\Bridge\Event\TwigPostRenderEvent
     */
    const POST_RENDER = 'theme.post_render';
