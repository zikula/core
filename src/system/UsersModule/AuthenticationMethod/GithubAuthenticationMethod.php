<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\AuthenticationMethod;

use League\OAuth2\Client\Provider\Github;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\ReEntrantAuthenticationmethodInterface;

class GithubAuthenticationMethod implements ReEntrantAuthenticationmethodInterface
{
    /**
     * @todo replace with repo lookup
     * @var array
     */
    private $userMap = [
        350048 => 2
    ];

    /**
     * @var Session
     */
    private $session;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * GithubAuthenticationMethod constructor.
     * @param Session $session
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     */
    public function __construct(Session $session, RequestStack $requestStack, RouterInterface $router)
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function getDisplayName()
    {
        return 'Github';
    }

    public function getDescription()
    {
        return 'Login using Github via OAuth.';
    }

    public function authenticate(array $data)
    {
        // @todo replace with module vars (id and secret)
        $provider = new Github([
            'clientId' => 'ec6ad84eb2d74acc2b12',
            'clientSecret' => 'a993eeda820b7c6176e4eb53524f78d464626ff2',
            'redirectUri' => $this->router->generate('zikulausersmodule_access_login', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        $request = $this->requestStack->getCurrentRequest();
        $state = $request->query->get('state', null);
        $code = $request->query->get('code', null);

        if (!isset($code)) {
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $this->session->set('oauth2state', $provider->getState());

            header('Location: ' . $authUrl);
            exit;

            // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($state) || ($state !== $this->session->get('oauth2state'))) {
            $this->session->remove('oauth2state');
            $this->session->getFlashBag()->add('error', 'Invalid State');

            return null;
        } else {
            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            try {
                // get the user's details
                $user = $provider->getResourceOwner($token);
                $this->session->getFlashBag()->add('success', sprintf('Hello %s!', $user->getNickname()));

                return $this->userMap[$user->getId()];
            } catch (\Exception $e) {
                $this->session->getFlashBag()->add('error', 'Could not obtain user details from Github.');

                return null;
            }

            // Use this to interact with an API on the users behalf
//            echo $token->getToken();
        }
    }
}
