Request/Response Cycle
=====================

The ThemeEngine works by intercepting the Response sent by the module controller (the controller action is the
'primary actor'). It takes this Response and "wraps" the theme around it and filters the resulting html to add
required page assets and variables and then sends the resulting Response to the browser. e.g.

    Request -> Controller -> CapturedResponse -> Filter -> ThemedResponse

In this altered Symfony Request/Response cycle, the theme can be altered by the Controller Action through Annotation.
The annotation only excepts defined values. See [ThemeAnnotation](../ThemeAnnotation.md) for more information.
