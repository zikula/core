# EU Cookie Compliance

The Legal extension allows automatic display of an EU Cookie Compliance notice on your website.
This must be manually enabled in the Legal admin settings. This functionality uses the script from
[jquery-cookiebar](https://www.primebox.co.uk/projects/jquery-cookiebar/) in order to implement this. More information is available on their
website.

A cookie is set on the users browser to indicate assent. This cookie expires after one year.

Notice:

- The current positioning of the notice is based on the Core's ZikulaDefaultTheme and the positioning MUST
  be adjusted if a different theme is used (because it is configured to append the `.navbar.fixed-top` element.)
  See below for customizing.

Options:

- Translation of notice and button text is available in default domain (`messages`).
- Override of stylesheet is allowed:
  Set a parameter value for `euwarning.stylesheet` in your `services.yaml` file and it will be used.
  Use full path from document root.
  Be sure to copy the existing stylesheet (`/Resources/public/js/jquery.cookiebar/jquery.cookiebar.css`)

## Config Customization

To create your own customization, you must override the JS file. Do so by copying the original JS from
`/src/extensions/zikula/legal-module/Resources/public/js/ZikulaLegalModule.Listener.EUCookieConfig.js`
to 
`/public/overrides/zikulalegalmodule/js/ZikulaLegalModule.Listener.EUCookieConfig.js`

Then alter the config options as you see fit. You can see the options in 
`/src/extensions/zikula/legal-module/Resources/public/js/jquery.cookiebar/jquery.cookiebar.js`

## Developer Info

The JS and stylesheet are injected directly into the Response via an Event Listener (`Listener/EuCookieWarningInjectorListener.php`).
