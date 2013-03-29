SensioGeneratorBundle
==========================

The ``SensioGeneratorBundle`` extends the default Symfony2 command line
interface by providing new interactive and intuitive commands for generating
code skeletons like modules, form classes or CRUD controllers based on a
Doctrine 2 schema.

Installation
------------

`Download`_ the module and put it under the ``Sensio\\Bundle\\`` namespace.
Then, like for any other module, include it in your Kernel class::

    public function registerBundles()
    {
        $modules = array(
            ...

            new Zikula\Bundle\GeneratorBundle\SensioGeneratorBundle(),
        );

        ...
    }

List of Available Commands
--------------------------

The ``SensioGeneratorBundle`` comes with four new commands that can be run in
interactive mode or not. The interactive mode asks you some questions to
configure the command parameters to generate the definitive code. The list of
new commands are listed below:

.. toctree::
   :maxdepth: 1

   commands/generate_module
   commands/generate_doctrine_crud
   commands/generate_doctrine_entity
   commands/generate_doctrine_form

.. _Download: http://github.com/sensio/SensioGeneratorBundle