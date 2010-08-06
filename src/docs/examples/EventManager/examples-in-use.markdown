<?php
    /* This is a fictitious example of events in action */
    // setup event manager
    $eventManager = new EventManager();
    $avatar = new Avatar();

    // attach a static callable Stuff::logLogin($event);
    $eventManager->attach('user.login', array('Stuff', 'logLogin'));

    // attach a read instanciated class
    $eventManager->attach('user.create', array($avatar, 'create'));  // $avatar->create($event);
    $eventManager->attach('user.create', array('Stuff', 'sendMail')); // Stuff::sendMail($event);

    $example = new Example($eventManager);
    $example->login('Joe');
    $example->create('Sally');

    class Example
    {
        protected $eventManager;

        public function __construct(EventManager $eventManager)
        {
            $this->eventManager = $eventManager;
        }

        public function login($user)
        {
            //... stuff here
            $event = new Event('user.login', null, array('username' => $user));
            $this->eventManager->notify($event);
        }

        public function create($username)
        {
            //... stuff here
            $user = new User($username);
            $user->save();
            $event = new Event('user.create', $user);
            $this->eventManager->notify($event);
        }
    }

    class Stuff
    {
        public static function logLogin(Event $event)
        {
            MyLogger::write($event->getName(), $event->getArg('username'), $_SERVER['REMOTE_ADDR']);
        }

        public function sendMail(Event $event)
        {
            if ($event->hasArg('emailaddress')) {
                mail($this->getArg('emailaddress'));
            }
        }
    }

    class Avatar
    {
        public function create(Event $event)
        {
            if ($event->getSubject() instanceof User) {
                $user = $event->getSubject();
                $user->addProperty('image', 'avatar.gif');
                $user->save();
            }
        }
    }

