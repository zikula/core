Dependency Injection on steroids
--------------------------------

The purpose of this class is to manage services (class objects).  These can be
existing instances, or more interestingly, models of services defined by the
Zikula\Common\ServiceManager\Definition class.  The ServiceManager is injected
into application classes. ServiceManager can manage single instances or multiple
instances of services so there is no longer a need to use difficult to test true
singleton patterns.  ServiceManager will lazy load classes on demand making it
extremely efficient.

The ServiceManager can do the following:

1. Attach an existing object by ID with attachService().
2. Detach an existing object by ID with detachService().

These are used to manage existing instanciated objects.

3. Register a service contained in a Service object with registerService().
4. Unregister a service by it's ID with unregisterService().

Once services are managed by ServiceManager you can request a service instance
with getService().

The classes in this package are ServiceManager, Service and Definition.

### ServiceManager class
This class manages services containers and will instanciate and objects according
to the required behaviour. All services are managed by a unique string identifier.

### Service class
This is a container for either instanciated classes or definitions that describe
how to instanciate a service.

### Definition class
This is a class that accurately describes how to instanciate a service.

### Argument class
This is a class that represents an argument stored in the ServiceManager.
The ServiceManager can be loaded with arguments (id => value) and these will
be looked up when instanciating a service that uses Argument definitions in
either constructor or method calls.

## Overview

Services can be shared or not.  A shared service means only the same single
instance will ever be returned. If the instance doesn't exist, it will be
created on the first request and stored.  Future requests for this object will
return the same stored object.

If a service is 'not shared' a new instance will be created (or cloned in certain
circumstances) each time the object is requested.

We can basically do either of the following, a) attach an existing instanciated
object, or b) assign a definition so the ServiceManager will know how to create
the object when requested.

a) Attach an existing instanciated object:

We simply attach an existing object using attachService($id, $object, $shared).

The default behaviour is for the object to be shared so the same object will be
returned again and again.  However, you can set $shared=false in which case the
object will be cloned each time.  This is a straight PHP clone, so you will be
responsible for any post clone processes which may involve attaching a __clone() method to handle instanciation.

b) Attach a service definition:

In this case you need to build up a definition of the class to be instanciated
with the Definition class.  This includes the class name, the constructor
arguments and any methods that need to be invoked on instanciation.  This is
then wrapped up in a Service class which provides the meta information, i.e.
the unique service identifier (string), and whether the service is shared or not.