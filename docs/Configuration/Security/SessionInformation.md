---
currentMenu: security-center
---
# Session Information

Sessions can be stored in the database or in the filesystem. This choice as well as a few configuration options
can be set in the ZikulaSecurityCenterModule UI:

The following configuration options are stored in `/config/packages/zikula_security_center.yaml`:

default values:

    zikula_security_center:
        x_frame_options: SAMEORIGIN
        session:
            name: _zsid
            handler_id: session.handler.native_file
            storage_id: zikula_core.bridge.http_foundation.zikula_session_storage_file
            save_path: '%kernel.cache_dir%/sessions'
            cookie_secure: auto

other possible values set via the UI:

            handler_id:
                `session.handler.native_file`
                or
                `zikula_core.bridge.http_foundation.doctrine_session_handler`
            storage_id:
                `zikula_core.bridge.http_foundation.zikula_session_storage_doctrine`
                or
                `zikula_core.bridge.http_foundation.zikula_session_storage_file`
