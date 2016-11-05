Theme Annotation
================

The action method of any controller can alter the theme through the use of Annotation.

This annotation is used in a controller method like so: 

    /**
     * @Route("/view")
     * @Theme("admin")
     * @return Response
     */

Possible values are:
 - "admin"
 - "print"
 - "atom"
 - "rss"
 - any valid theme name (e.g. "ZikulaAndreas08Theme")

The class must be imported in order to function.

    use Zikula\ThemeModule\Engine\Annotation\Theme;
