<?php $view->extend('::base.html.twig') ?>

<?php $view['slots']->set('title', '{{ module }}:{{ controller }}:{{ action.basename }}') ?>

<?php $view['slots']->start('body') ?>
    <h1>Welcome at the {{ controller }}:{{ action.basename }} page</h1>
<?php $view['slots']->stop() ?>
