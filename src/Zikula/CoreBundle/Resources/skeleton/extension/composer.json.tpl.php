{
    "name": "<?php echo mb_strtolower($vendor); ?>/<?php echo mb_strtolower($name); ?>-<?php echo mb_strtolower($type); ?>",
    "version": "1.0.0",
    "description": "This is a description.",
    "type": "zikula-<?php echo mb_strtolower($type); ?>",
    "license": "MIT",
    "authors": [
        {
            "name": "<?php echo $vendor; ?> Team"
        }
    ],

    "autoload": {
        "psr-4": { "<?php echo str_replace('\\', '\\\\', $namespace); ?>\\": "" }
    },
    "require": {
        "php": ">=7.2.5"
    },
    "extra": {
        "zikula" : {
            "core-compatibility": ">=3.0.0",
            "class": "<?php echo str_replace('\\', '\\\\', $namespace); ?>\\<?php echo $bundleClass; ?>",
            "displayname": "<?php echo $bundleClass; ?>",
            "icon": "fas fa-layer-group",
            "url": "<?php echo mb_strtolower($vendor . $name . $type); ?>",
<?php if ('Module' === $type) { ?>
            "securityschema": {
                "<?php echo $vendor . $name . $type; ?>::": "::"
            },
<?php } ?>
            "capabilities": {
<?php if ('Theme' === $type) { ?>
                "user": {
                    "theme": true
                }
<?php } ?>
            }
        }
    }
}
