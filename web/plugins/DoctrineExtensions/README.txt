DOCTRINE EXTENSIONS
===================

This plugin provides the DoctrineExtensions from https://github.com/l3pp4rd/DoctrineExtensions
and https://github.com/beberlei/DoctrineExtensions

Documentation is available the project pages.

The plugin simply adds the Gedmo namespace to the autoloaders so you
can just reference the classes directly.  Ignore the instructions
in the vendors documentation.

Example of use in Zikula:

    // get entitymanager
    $entityManager = $this->serviceManager->getService('doctrine.entitymanager');

    // get the doctrine event manager
    $evm = $entityManager->getEventManager();

    // create a listener (doctrine extension)
    $sluggableListener = new \Gedmo\Sluggable\SluggableListener();

    // attach the listener
    $evm->addEventSubscriber($sluggableListener);

