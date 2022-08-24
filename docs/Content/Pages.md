---
currentMenu: content-pages
---
# Static Pages

Zikula includes a `StaticContentBundle` providing means for very basic **template-driven** static content.

Simply create a template with the needed content in `/templates/p/` like `/templates/p/apple.html.twig`.
Then direct your browser to `/p/apple` and the template will be displayed.

Any standard HTML can be used as well as any template functions and global template variables. 

- `{{ siteName() }}`
- `{{ pagevars.title }}`
- `{{ currentUser.loggedIn }}`
- `{{ app.request.method }}`
- `{{ pageAddAsset('javascript', asset('/path/from/public/to/my/javascript.js')) }}`
- etc...

