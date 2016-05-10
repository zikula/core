<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\LinkContainer\LinkContainerInterface;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("")
     * @Template
     * @param Request $request
     * @return Response|array
     */
    public function menuAction(Request $request)
    {
        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_user_login', ['returnpage' => urlencode($this->get('router')->generate('zikulausersmodule_user_index'))]);
        }

        if (!$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // get the menu links for Core-2.0 modules
        $accountLinks = $this->get('zikula.link_container_collector')->getAllLinksByType(LinkContainerInterface::TYPE_ACCOUNT);
        $legacyAccountLinksFromNew = [];
        foreach ($accountLinks as $moduleName => $links) {
            foreach ($links as $link) {
                $legacyAccountLinksFromNew[] = [
                    'module' => $moduleName,
                    'url' => $link['url'],
                    'text' => $link['text'],
                    'icon' => $link['icon']
                ];
            }
        }

        // @deprecated The API function is called for old-style modules
        $legacyAccountLinks = \ModUtil::apiFunc('ZikulaUsersModule', 'user', 'accountLinks');
        if (false === $legacyAccountLinks) {
            $legacyAccountLinks = [];
        }
        // add the arrays together
        $accountLinks = $legacyAccountLinksFromNew + $legacyAccountLinks;

        return ['accountLinks' => $accountLinks];
    }
}
