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

namespace Zikula\UsersBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsBundle\Api\VariableApi;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\UsersBundle\Collector\AuthenticationMethodCollector;
use Zikula\UsersBundle\Constant as UsersConstant;
use Zikula\UsersBundle\Form\Type\ConfigType\AuthenticationMethodsType;
use Zikula\UsersBundle\Form\Type\ConfigType\ConfigType;

/**
 * @PermissionCheck("admin")
 */
#[Route('/users')]
class ConfigController extends AbstractController
{
    /**
     * @Theme("admin")
     * @Template("@ZikulaUsersBundle/Config/config.html.twig")
     */
    #[Route('/config', name: 'zikulausersbundle_config_config')]
    public function config(
        Request $request,
        EventDispatcherInterface $eventDispatcher
    ): array {
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
            'UC' => new UsersConstant(),
        ];
    }

    /**
     * @Theme("admin")
     * @Template("@ZikulaUsersBundle/Config/authenticationMethods.html.twig")
     *
     * @return array|RedirectResponse
     */
    #[Route('/config/authentication-methods', name: 'zikulausersbundle_config_authenticationmethods')]
    public function authenticationMethods(
        Request $request,
        VariableApiInterface $variableApi,
        AuthenticationMethodCollector $authenticationMethodCollector,
        CacheClearer $cacheClearer
    ) {
        $allMethods = $authenticationMethodCollector->getAll();
        $authenticationMethodsStatus = $variableApi->getSystemVar('authenticationMethodsStatus', []);
        foreach ($allMethods as $alias => $method) {
            if (!isset($authenticationMethodsStatus[$alias])) {
                $authenticationMethodsStatus[$alias] = false;
            }
        }
        // remove invalid values (if modules has been removed, etc)
        foreach ($authenticationMethodsStatus as $alias => $enabled) {
            if (!isset($allMethods[$alias])) {
                unset($authenticationMethodsStatus[$alias]);
            }
        }
        $variableApi->set(VariableApi::CONFIG, 'authenticationMethodsStatus', $authenticationMethodsStatus);

        $form = $this->createForm(AuthenticationMethodsType::class, [
            'authenticationMethodsStatus' => $authenticationMethodsStatus
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                if (!in_array(true, $data['authenticationMethodsStatus'], true)) {
                    // do not allow all methods to be inactive.
                    $data['authenticationMethodsStatus']['native_either'] = true;
                    $this->addFlash(
                        'info',
                        $this->trans(
                            'All methods cannot be inactive. At least one methods must be enabled (%method% has been enabled).',
                            ['%method%' => $allMethods['native_either']->getDisplayName()]
                        )
                    );
                }
                $variableApi->set(VariableApi::CONFIG, 'authenticationMethodsStatus', $data['authenticationMethodsStatus']);
                $this->addFlash('status', 'Done! Configuration updated.');

                // clear cache to reflect the updated state (#3936)
                $cacheClearer->clear('symfony');
                $cacheClearer->clear('twig');

                return $this->redirectToRoute('zikulausersbundle_config_authenticationmethods');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return [
            'form' => $form->createView(),
            'methods' => $allMethods,
        ];
    }
}
