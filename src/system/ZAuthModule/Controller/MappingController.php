<?php

namespace Zikula\ZAuthModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class MappingController
 */
class MappingController extends AbstractController
{
    /**
     * @Route("/list")
     * @Template
     * @Theme("admin")
     */
    public function listAction()
    {
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $mappings = $this->get('zikula_zauth_module.authentication_mapping_repository')->findAll();

        return [
            'mappings' => $mappings
        ];
    }
}
