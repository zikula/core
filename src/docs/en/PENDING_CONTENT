PENDING CONTENT
===============

This document details the get.pending_content event which is used to collect
information from modules about pending content items like news submissions or
similar.

Modules that wish to publish this information should create a persistent handler
for 'get.pending_content'

Here is an exmaple handler:

    [php]
    // event handler
    class News_Handlers
    {
        public static function handler(Zikula_Event $event)
        {
            $collection = new Zikula_Collection_Container('News');
            $collection->add(new Zikula_Provider_AggregateItem('submission', __('pending news'), 5, 'Admin', 'viewsubmissions'));
            $collection->add(new Zikula_Provider_AggregateItem('comments', __('pending comments'), 7, 'Admin', 'viewcomments'));
            $event->getSubject()->add($collection);
        }
    }


While you don't particularly need to know about this unless you want to collect
this information and process it, here is an example implementaion which will
collect the data and create a list of links to the pending content views of
each module:

    [php]
    // trigger event
    $event = new Zikula_Event('get.pending_content', new Zikula_Collection_Container('pending_content'));
    $pendingCollection = EventUtil::getManager()->notify($event)->getSubject();

    // process results
    foreach ($pendingCollection as $collection) {
        $module = $collection->getName();
        foreach ($collection as $item) {
            $link = ModUtil::url($module, $item->getController(), $item->getMethod(), $item->getArgs());
            echo "{$item->getDescription()}: <a href='$link'>{$item->getNumber()}</a><br />\n";
        }
    }

The following is a full example you can use to run and see how this would work in practice:

    [php]
    include 'lib/ZLoader.php';
    ZLoader::register();
    System::init();

    // setup test
    EventUtil::getManager()->attach('get.pending_content', array('News_Handlers', 'handler'));

    // trigger event
    $event = new Zikula_Event('get.pending_content', new Zikula_Collection_Container('pending_content'));
    $pendingCollection = EventUtil::getManager()->notify($event)->getSubject();

    // process results
    foreach ($pendingCollection as $collection) {
        $module = $collection->getName();
        foreach ($collection as $item) {
            $link = ModUtil::url($module, $item->getController(), $item->getMethod(), $item->getArgs());
            echo "{$item->getDescription()}: <a href='$link'>{$item->getNumber()}</a><br />\n";
        }
    }

    // event handler
    class News_Handlers
    {
        public function handler(Zikula_Event $event)
        {
            $collection = new Zikula_Collection_Container('News');
            $collection->add(new Zikula_Provider_AggregateItem('submission', __('pending news'), 5, 'Admin', 'viewsubmissions'));
            $collection->add(new Zikula_Provider_AggregateItem('comments', __('pending comments'), 7, 'Admin', 'viewcomments'));
            $event->getSubject()->add($collection);
        }
    }

