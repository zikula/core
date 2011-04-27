EventManager Documentation
--------------------------

EventManager is a fully unit tested library that connects events to event handlers through the observer pattern.  It's purpose is to connect two end points together in such a way that neither need to have any knowledge of eachother.  There are some fascinating possibilities with this library.  Although this library was written specifically as part of the zOO toolkit, it's completely decoupled so it can be used in anything.

In order to understand how this library functions we need to break it down into three parts: EventManager, the Handler and the Event.

1. The EventManager class manages event handlers.
2. Handlers are anything that is callable by PHP, like functions and classes.  They 'listen for' and handle events when appropriate.
3. Events are any data encapsulated in an Event class instance.  This is passed by the EventManager to the handlers.

All event handlers must have an event name.  Handlers will be attached to this event name.  An event name may have multiple handlers attached e.g.

Event name: user.login

Handler 1: a logging function.
Handler 2: a notify by email function.

We attach handlers using EventManager.  Events handlers must be a PHP callable, meaning any PHP callable code: function, a class etc.

    [php]
    $eventManager = new EventManager();
    $logger= new Logger();
    $eventManager->attach('user.login', array($logger, 'writelog');

The handler method $logger->writelog($event) will now be called whenever anything notifies that a 'user.login' event has occurred.

    [php]
    $event = new Event('user.log', $subject, $parameters);
    $eventManager->notify($event);

### A look at the Event class

The Event class is used to encapsulate the event and acts as a go-between.  This allows a unified way for handlers to interact with events without knowing about 'who' called it, and without the calling class having to know about the event handler.

The event must have a name which will be used to call all event handlers also registered with that name.  Events may also contain an event 'subject' and parameters that will be passed to the event handler.  This is where the magic is as the event can pass parameters and even the calling object class, like $this.

This encapsulated data allows the event handler to decide if it should execute or not by querying the Event object.

#### HANDLER TYPES
A closer look at event handlers.

There are two types of event handler, 'notify' and 'notifyUntil' handlers:

Event handlers receive an instance of the Event object and are processed blindly. They do not pass a return value.  There are two kinds of notify event handlers
1. notify() handlers simply execute with no return.
2. notify() handlers must call $event->stop() if they executed.  No more handlers will be notified of events after this one exits.

Event handlers receive the event object and have full access to whatever was encapsulated by the event object.

    [php]
    $event->getName()
    $event->getSubject()
    $event->getArgs()
    $event->getArg()
    $event->hasArg()
    $event->getData()

For convenience Event objects use ArrayAccess on the $args property, so you may access $args[$key] by $event[$key].

Post event call you can also do processing with events that were called with notify() e.g.

    [php]
    if ($event->isStopped()) {
        //..
    }

etc.

## ADVANCED EVENT HANDLERS

If using the EventManager in conjunction with the ServiceManager, then you may additionally encapsulate event handlers in the ServiceHandler class which takes the service ID and the method to call in the constructor.  This will allow you to load up event handlers in the EventManager for services which have not yet been instanciated - they will be created on demand.

    [php]
    $eventManager = new EventManager($serviceManager);
    $eventManager->attach('eventname', new ServiceHandler('some.service', 'handlerMethod'));

## EVENT WEIGHTING

It is possible to influence the execution order of event handlers.
Handlers registered against a name can be set a weight which defaults at 0. If
more than one handler is registered per weighting order then they will be executed
in the order they were attached.  Weights can be positive or negative.  Use the
thrid parameter in the attach method.
