# Session Information

Sessions can be stored in the database or in the filesystem. This choice as well as a few configuration options
can be set in the ZikulaSecurityCenterModule:

The following configuration options are converted to a dynamic config and stored in `config/dynamic/generated.yaml`
 - zikula.session.name: `_zsid`
 - zikula.session.handler_id:
    `session.handler.native_file`
    or
    `zikula_core.bridge.http_foundation.doctrine_session_handler`
 - zikula.session.storage_id:
    `zikula_core.bridge.http_foundation.zikula_session_storage_doctrine`
    or
    `zikula_core.bridge.http_foundation.zikula_session_storage_file`
 - zikula.session.save_path: `%kernel.cache_dir%/sessions` # default symfony value
