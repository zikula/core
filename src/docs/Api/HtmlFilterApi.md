HtmlFilterApi
=============

classname: \Zikula\SecurityCenterModule\Api\HtmlFilterApi

service id = "zikula_security_center_module.api.html_filter_api"

This class provides a method to filter allowable content from an Html string.

The class makes the following methods available:

    /**
     * Filter an html string (or array of strings) and remove disallowed tags
     *
     * @param string|array $value
     * @return string|array
     */
    public function filter($value);
