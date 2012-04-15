<?php

namespace Zikula\ThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    public function welcomeAction()
    {
        return $this->render('ZikulaThemesBundle:Default:welcome.html.twig');
    }
}
