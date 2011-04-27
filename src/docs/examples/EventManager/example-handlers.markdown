Example Event Handler methods
-----------------------------

Here we have examples of handlers.  Remember $event->args property contains
whatever was encapulated when creating the $event. The events also have access
to the $event->subject property which may contain something like $this.

    [php]
    // Example event static method handler
    class Loggger
    {
        //... other unrelated methods here
        public static function logUser(Event $event)
        {
            self::write($event->getName(), $event->getArg('username'), $_SERVER['REMOTE_ADDR']);
            // USING ARRAY ACCESS YOU CAN ALSO DO:
            // self::write($event->getName(), $event['username'], $_SERVER['REMOTE_ADDR']);
        }
    }

The folllowing show an example of one of each kind of event handler, notify, notifyUntil, process and processUntil.

    [php]
    class HandlerExamples
    {
       /**
        * Notify handler.
        *
        * This kind of handler is called by $eventManager->notify($event)
        * This handler must silently perform it's task but should not return
        * anything.
        *
        * @param Event $event
        *
        * @return object The Event object.
        */
        public function handler(Event $event)
        {
            if (isset($event['type']) && $event['type'] == 'login') {
                //... do something
            }
        }

        /**
         * NotifyUntil handler.
         *
         * This type of handler is called by $eventManager->notify($event) which
         * will keep calling all registered handlers of the name until one
         * responds.
         *
         * This handler should check to see if it should execute and if so it
         * must call $event->stop();.
         *
         * @param Event $event
         *
         * @return object The Event object.
         */

        public function handlerUntil(Event $event)
        {
            if ($event->getHas('foo')) {
                $event->stop();
            }
        }

       /**
        * Notify handler that process a return value.
        *
        * This kind of handler is called by $eventManager->notify($event)
        * This handler returns data via the $event->data property which
        * has been made public for conventience although has getData() and setData.
        * Using this method you can process or filter some data.  How this is done
        * is entirely by convention of the event.
        *
        * @param Event $event
        *
        * @return object The Event object.
        */
        public function handler(Event $event)
        {
            $event->data++;

            // equally you can do it the long way.
            $data = $event->getData();
            $data++;
            $event->setData($data);
        }

       /**
        * NotifyUntil handler that process a return value.
        *
        * This kind of handler is called by $eventManager->notify($event)
        * This handler returns data via the $event->data property which
        * has been made public for conventience although has getData() and setData.
        * Using this method you can process or filter some data.  How this is done
        * is entirely by convention of the event.
        *
        * @param Event $event
        *
        * @return object The Event object.
        */
        public function handler(Event $event)
        {
            if (!isset($event['type']) == 'foo') {
                return;
            }

            $event->data++;
            $event->stop();
        }
    }

