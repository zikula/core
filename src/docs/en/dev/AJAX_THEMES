PURE AJAX IN THEMES
===================
It is now possible to process ajax directly inside a theme.  To call and AJAX
controller, simply invoke the following along with any additional parameters
required.

    ajax.php?module=theme&func=dispatch

Inside the theme, you must implement a 'theme.ajax_request' event handler.  Since
there will only be one theme instanciated at any time, only the handlers of that
theme will be attached to the EventManager.  The handler should make tests to see
if show run in any case, possible testing for the presence of certain $_GET, or
$_POST - do this without validation, but of course, sanitize and validate if
the handler must execute.

The handler must return it's output by doing `$event->setData($data)` and
notify the fact it executed with `$event->stop()`. An example follows

    class Themes_Foo_EventHandler_Handlers
    {
        protected $eventNames = array('theme.ajax_request' => 'ajaxHandler');

        public function ajaxHandler(Zikula_Event $event)
        {
            // check if we should execute
            if (!isset($_GET['sort'])) {
                return;
            }

            //... do something
            $event->setData($output);
            $event->stop();
        }
    }

Place this file in the theme's lib/Foo/EventHandler folder - these are loaded
automatically when the theme is initialised at System::init()

Themes make use of autoloading so you can address any class in the same way as
in a module but the namespace always starts with Themes_$name and maps to the
theme's lib/ folder in the same way as modules.
