<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Template
     * @Theme("admin")
     *
     * @param Request $request
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     *
     * @return array|Response
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $modVars = $variableApi->getAll('ZikulaCategoriesModule');
        $modVars['userrootcat'] = $this->get('zikula_categories_module.category_repository')->find($modVars['userrootcat']);
        $form = $this->createForm('Zikula\CategoriesModule\Form\Type\ConfigType',
            $modVars, [
                'translator' => $this->get('translator.default'),
                'locale' => $request->getLocale()
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $modVars = $form->getData();
                $modVars['userrootcat'] = $modVars['userrootcat']->getId();
                $variableApi->setAll('ZikulaCategoriesModule', $modVars);
                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
