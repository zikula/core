<?php

namespace Zikula\Bundle\ModuleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LegacyController extends Controller
{
    public function rerouteAction($module, $type, $action)
    {
        return $this->forward('Zikula'.ucfirst($module)."Module:".$type.":".$action);
    }


}
