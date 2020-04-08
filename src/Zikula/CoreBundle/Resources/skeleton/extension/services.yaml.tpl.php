services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    <?php echo $namespace; ?>\:
        resource: '../../*'
        exclude: '../../{bootstrap.php,Tests,vendor}'
