<?php

namespace Zikula\ZAuthModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var CurrentUserApi
     */
    private $currentUser;

    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $mappingRepository;

    /**
     * constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param PermissionApi $permissionApi
     * @param VariableApi $variableApi
     * @param CurrentUserApi $currentUserApi
     * @param AuthenticationMappingRepositoryInterface $mappingRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApi $permissionApi,
        VariableApi $variableApi,
        CurrentUserApi $currentUserApi,
        AuthenticationMappingRepositoryInterface $mappingRepository
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->currentUser = $currentUserApi;
        $this->mappingRepository = $mappingRepository;
    }

    /**
     * get Links of any type for this extension
     * required by the interface
     *
     * @param string $type
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        $method = 'get' . ucfirst(strtolower($type));
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return [];
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = [];
        if ($this->permissionApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_list'),
                'text' => $this->translator->__('Users list'),
                'icon' => 'list'
            ];
            $links[] = [
                'url' => $this->router->generate('zikulazauthmodule_config_config'),
                'text' => $this->translator->__('Settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    private function getUser()
    {
        $links = $this->getAccount();
        array_unshift($links, [
            'url'   => $this->router->generate('zikulausersmodule_account_menu'),
            'text' => $this->translator->__('Menu'),
            'icon'  => 'cubes'
        ]);
        $links[] = [
            'icon' => 'key',
            'links' => [
                [
                    'text' => $this->translator->__('Recover Lost User Name'),
                    'url' => $this->router->generate('zikulazauthmodule_account_lostusername')
                ],
                [
                    'text' => $this->translator->__('Recover Lost Password'),
                    'url' => $this->router->generate('zikulazauthmodule_account_lostpassword')
                ],
                [
                    'text' => $this->translator->__('Enter Password Recovery Code'),
                    'url' => $this->router->generate('zikulazauthmodule_account_confirmationcode')
                ]
            ],
            'text' => $this->translator->__('Recover account information or password'),
            'url' => $this->router->generate('zikulausersmodule_account_menu'),
        ];

        return $links;
    }

    private function getAccount()
    {
        $links = [];
        if (!$this->currentUser->isLoggedIn()) {
            return $links;
        }

        $userMapping = $this->mappingRepository->findOneBy(['uid' => $this->currentUser->get('uid')]);
        if (isset($userMapping)) {
            $links[] = [
                'url'   => $this->router->generate('zikulazauthmodule_account_changepassword'),
                'text' => $this->translator->__('Change password'),
                'icon'  => 'key text-success'
            ];
        }

        $links[] = [
            'url'   => $this->router->generate('zikulazauthmodule_account_changeemail'),
            'text' => $this->translator->__('Change e-mail address'),
            'icon'  => 'at'
        ];

        return $links;
    }

    /**
     * set the BundleName as required by the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'ZikulaZAuthModule';
    }
}
