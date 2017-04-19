Generating a New Doctrine Entity Stub
=====================================

Usage
-----

The `zikula:generate:doctrine:entity` command generates a new Doctrine entity stub
including the mapping definition and the class properties, getters and setters.

By default the command is run in the interactive mode and asks questions to
determine the entity, fields, format and if a repository should also be generated:

    php app/console zikula:generate:doctrine:entity

The command can be run in a non interactive mode by using the
`--non-interaction` option without forgetting all needed options:

    php app/console zikula:generate:doctrine:entity --non-interaction --entity=AcmeBlogModule:Post --fields="title:string(100) body:text" --format=xml

Available Options
-----------------

* `--entity`: The entity name given as a shortcut notation containing the
  module name in which the entity is located and the name of the entity. For
  example: `AcmeBlogModule:Post`:

    `php app/console zikula:generate:doctrine:entity --entity=AcmeBlogModule:Post`

* `--fields`: The list of fields to generate in the entity class:

    `php app/console zikula:generate:doctrine:entity --fields="title:string(100) body:text"`

* `--format`: (**annotation**) [values: yml, xml, php or annotation] This
  option determines the format to use for the generated configuration files
  like routing. By default, the command uses the `annotation` format:

    `php app/console zikula:generate:doctrine:entity --format=annotation`

* `--with-repository`: This option tells whether or not to generate the
  related Doctrine `EntityRepository` class:

    `php app/console zikula:generate:doctrine:entity --with-repository`
