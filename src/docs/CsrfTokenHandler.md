CsrfTokenHandler Service
========================

classname: \Zikula\Core\Token\CsrfTokenHandler

service id="zikula_core.common.csrf_token_handler"

This class handles Csrf tokens that are needed outside the Symfony Form Library (which handles tokens automatically). 
These can be useful as GET parameters or in non-Symfony forms.

methods
-------

    `generate($forceUnique = false)`
    
Generate a Csrf token.

    `validate($token = null, $invalidateSessionOnFailure = false)`
    
Validate a Csrf token.