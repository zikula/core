Generating a New Controller
===========================

Usage
-----

The `zikula:generate:controller` command generates a new Controller including 
actions, tests, templates and routing.

By default the command is run in the interactive mode and asks questions to
determine the controller, actions and format:

    $ php bin/console zikula:generate:controller

The command can be run in a non interactive mode by using the
`--no-interaction` option without forgetting all needed options:

    $ php bin/console zikula:generate:controller --no-interaction --controller=AcmeBlogModule:Post

Available Options
-----------------

* `--controller`: The controller name given as a shortcut notation containing 
  the module name in which the controller is located and the name of the
  module. For instance: `AcmeBlogModule:Post` (creates a `PostController`
  inside the `AcmeBlogModule` module):

    `$ php bin/console zikula:generate:controller --controller=AcmeBlogModule:Post`

* `--actions`: The list of actions to generate in the controller class. This
  has a format like `%actionname%:%route%:%template` (where `:%template%`
  is optional:

    `$ php bin/console zikula:generate:controller --actions="showPostAction:/article/{id} getListAction:/_list-posts/{max}:AcmeBlogModule:Post:list_posts.html.twig"`
    
    // or

    `$ php bin/console zikula:generate:controller --actions=showPostAction:/article/{id} --actions=getListAction:/_list-posts/{max}:AcmeBlogModule:Post:list_posts.html.twig`

* `--format`: (**annotation**) [values: yml, xml, php or annotation] 
  This option determines the format to use for routing. By default, the 
  command uses the `annotation` format:

    `$ php bin/console zikula:generate:controller --route-format=annotation`
