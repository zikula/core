ServiceIdTrait
==============

name: \Zikula\Bundle\HookBundle\ServiceIdTrait

Adds the following properties and methods to your class:

    private $serviceId;

    public function getServiceId();

    public function setServiceId($serviceId);

This is used in `\Zikula\Bundle\HookBundle\Collector\HookCollector::addProvider` as one example.
