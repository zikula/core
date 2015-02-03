CUSTOMISATION
-------------
Here are a few short hints which become helpful for customising your generated application:

    * Your model is the real software so do all important changes (like adding or moving table columns) on model level.
      Do not let your model become obsolete, which means losing lots of advantages.
    * Do all cosmetic enhancements by template overriding:
        - placing them in /config/templates/ for example is a good idea for development.
    * If you need display-oriented additional logic, simply create a render plugin encapsulating your efforts
      in a file which is not affected by the generator.
    * Perform logical enhancements in the domain classes.
      The Base classes contain generated code, while the actual objects extend from them.
      So you can do all customisations in the empty classes, keeping your manual code separated.
    * The controller util class contains some convenience methods which can be easily used to enable/disable
      certain use cases (like view, display, ...) for particular object types within custom conditions.
    * Document your changes to simplify merging process after regeneration.
      Be sure you will need and love it: add some fields later on, get a new generator version fixing
      some bugs centrally, benefit from new features, and so on.
    * A version control system gives you another additional level of rollback safety.

