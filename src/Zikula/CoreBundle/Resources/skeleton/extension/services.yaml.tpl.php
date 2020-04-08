services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false
        bind:
            $extension: '@<?php echo $namespace; ?>\<?php echo $bundleClass ?>'

    <?php echo $namespace; ?>\:
        resource: '../../*'
        exclude: '../../{bootstrap.php,Tests,vendor}'
