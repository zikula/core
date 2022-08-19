<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\ZAuthBundle\Form\Type\ConfigType;
use Zikula\ZAuthBundle\ZAuthConstant;

/**
 * @PermissionCheck("admin")
 */
#[Route('/zauth')]
class ConfigController extends AbstractController
{
    /**
     * @Theme("admin")
     * @Template("@ZikulaZAuth/Config/config.html.twig")
     */
    #[Route('/config', name: 'zikulazauthbundle_config_config')]
    public function config(Request $request): array
    {
        $form = $this->createForm(ConfigType::class, $this->getVars());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                $this->setVars($data);
                $this->addFlash('status', 'Done! Configuration updated.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return [
            'form' => $form->createView(),
            'ZAC' => new ZAuthConstant(),
        ];
    }
}
