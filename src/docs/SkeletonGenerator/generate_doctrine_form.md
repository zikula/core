# Generating a new form type class based on a Doctrine entity

## Usage

The `zikula:generate:doctrine:form` generates a basic form type class by using the
metadata mapping of a given entity class:

    php bin/console zikula:generate:doctrine:form AcmeBlogModule:Post

## Required arguments

* `entity`: The entity name given as a shortcut notation containing the
  module name in which the entity is located and the name of the entity. For
  example: `AcmeBlogModule:Post`:

    `php bin/console zikula:generate:doctrine:form AcmeBlogModule:Post`
