Example of how to use the ServiceManager
----------------------------------------

Start the ServiceManager.  This becomes the dependency injection container you would
inject into your application:

    [php]
    $serviceManager = new ServiceManager();

## Attaching already instanciated services.

    [php]
    $foo = new Foo(); // some class
    $serviceManager->attachService('example.foo', $foo);

    // retrieve the shared service
    $test = $serviceManager->getService('example.foo');

Remember if you use $serviceManager->attachService('example.foo', $foo, false);
then you will will always receive a php clone of the object, so you will need to
follow all the normal cautions of object cloning.

In most cases you will want to build definitions of classes so they can be
instanciated via lazy loading on demand.

## Building a definition for a (non-shared) multiple instance class.

    [php]
    $bar = new Bar(); // what we will emulate

The following code will emulate the above example.

    [php]
    $definitionBar = new Definition('Bar');
    $serviceManager->registerService(new Service('example.bar', $definitionBar, false));

When you need a 'example.bar' service, issue the following command.  Because
the definition is for a multiple instance, you will get a new Bar each time.

    [php]
    $bar = $serviceManager->getService('example.bar');

## Building a definition for single instance services (shared services)

    [php]
    $eventManager = new EventManager(); // what we will replicate in a definition

The following code will replicate the above example as a shared service (single instance).

    [php]
    $definition = new Definition('EventManager');
    $serviceManager->registerService(new Service('example.eventmanager', $definition, true));

The first time we use `$serviceManager->getService('example.eventmanager');`
it will instanciate the instance and store it.  Each subsequent call to
`$serviceManager->getService('example.eventmanager');` will return the same
instance - much like a singleton.

First time called, creates the service. Second time call, the same instance will be returned.
In the following example $eventManager === $eventManager2

    [php]
    $eventManager = $serviceManager->getService('example.eventmanager');
    $eventManager2 = $serviceManager->getService('example.eventmanager');


## Building definitions with constructor arguments and methods
    
The following is what we want to build as a definition:

    [php]
    $user = new User('female', 'blonde');
    $user->setLikes('pizza');
    $user->initialise();

The following example will replicate the above code in a definition.

    [php]
    $definitionUser = new Definition('User');
    $definitionUser->setConstructorArgs(array('female', 'blonde'));
    $definitionUser->addMethod('setLikes', array('pizza'));
    $definitionUser->addMethod('initialise');
    $serviceManager->registerService(new Service('example.user', $definitionUser, false));

Get a new female blonde user who likes pizza on demand.

    $user = $serviceManager->getService('example.user');

## Passing other classes as constructor (or method) arguments.

Sometimes it is necessary to pass classes as arguments, you can do this in three ways.

Example for the following code can be replicated in the following ways:

    [php]
    $cd = new Cd();
    $player = new Player($cd);

Option 1 - Pass a service definition.

    [php]
    $cdDef = new Definition('Cd', array('Bettles'));
    $playerDef = new Definition('Player', array($cdDef));
    $serviceManager->registerService(new Service('cdplayer', $playerDef));

Option 2 - Pass a reference to an registered service definition.

    [php]
    $cdDef = new Definition('Cd', array('Bettles'));
    $serviceManager->registerService(new Service('bettles.cd', $cdDef));
    $playerDef = new Definition('Player', array(new Service('bettles.cd')));
    $serviceManager->registerService(new Service('cdplayer', $playerDef));

Option 3 - Pass reference to an attached service

    [php]
    $cd = new Cd('Beetles');
    $serviceManager->attachService('bettles.cd', $cd);
    $playerDef = new Definition('Player', array(new Service('bettles.cd')));
    $serviceManager->registerService(new Service('cdplayer', $playerDef));

## Representing dynamic arguments.

Sometimes it will be necessary to configure classes dynamically. Possibly your
application will load a separate configuration file.  These configurations can
be loaded into the ServiceManager.  Argument classes can be used to represent these
stored values that will be looked up when creating the service.

    [php]
    $argument = new Argument('welcomemessage');
    $definition = new Definition('Foo', array($argument));
    $serviceManager->registerService(new Service('foo_service', $definition));
    $serviceManager->setArgument('welcomemessage', 'Hello world!');

Now when the class is instanciated it will be instanciated with as if

    [php]
    $class = new Foo('Hello world!');


