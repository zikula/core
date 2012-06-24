<?php

namespace Zikula\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class DefaultController
{
    public function homepageAction()
    {
        // todo - this is where we can do start page detection based on administrative settings.

        return new Response('homepage');
    }
}
