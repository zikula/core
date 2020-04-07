{
    "name": "<?php echo mb_strtolower($vendor); ?>/<?php echo mb_strtolower($name) ?>-<?php echo mb_strtolower($type); ?>",
    "version": "1.0.0",
    "description": "This is a description.",
    "type": "zikula-<?php echo mb_strtolower($type) ?>",
    "license": "MIT",
    "autoload": {
        "psr-4": { "<?php echo str_replace('\\', '\\\\', $namespace . $type); ?>\\": "" }
    },
    "require": {
        "php": ">=7.2.5"
    },
    "extra": {
        "zikula" : {
            "core-compatibility": ">=3.0.0",
            "class": "<?php echo str_replace('\\', '\\\\', $bundleClass); ?>",
            "displayname": "<?php echo ucfirst($name) . ' ' . ucfirst($type) ?>",
            "icon": "fas fa-layer-group",
            "capabilities": {
                "user": {
                    "theme": true
                }
            }
        }
    }
}
