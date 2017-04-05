Session Information
===================

Sessions can be stored in the Database or in the Filesystem. This choice as well as a few configuration options
can be set in the ZikulaSecurityModule:

The following configuration options are converted to a dynamic config and stored in `/src/app/config/dynamic/generated.yml`
 - zikula.session.name: _zsid
 - zikula.session.handler_id: 
    'session.handler.native_file' #default 
    or 
    'zikula_core.bridge.http_foundation.doctrine_session_handler'
 - zikula.session.storage_id: 
    'zikula_core.bridge.http_foundation.zikula_session_storage_doctrine' #default 
    or 
    'zikula_core.bridge.http_foundation.zikula_session_storage_file'
 - zikula.session.save_path: '%kernel.cache_dir%/sessions' #default symfony value
