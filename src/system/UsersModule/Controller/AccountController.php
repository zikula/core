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

    /**
     * @Route("/lost-user-name")
     * @Template
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function lostUserNameAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $form = $this->createForm('Zikula\UsersModule\Form\Account\Type\LostUserNameType',
            [], ['translator' => $this->get('translator.default')]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            $user = $this->get('zikula_users_module.user_repository')->findBy(['email' => $data['email']]);
            if (count($user) == 1) {
                // send email
                $sent = $this->get('zikula_users_module.helper.mail_helper')->mailUserName($user[0]);
                if ($sent) {
                    $this->addFlash('status', $this->__f('Done! The account information for %s has been sent via e-mail.', ['%s' => $data['email']]));
                }
            } elseif (count($user) > 1) {
                // too many users
                $this->addFlash('error', $this->__('There are too many users registered with that address. Please contact the system administrator for assistance.'));
            } else {
                // no user
                $this->addFlash('error', $this->__('Unable to send email to the request address. Please contact the system administrator for assistance.'));
            }
        }

        return [
            'form' => $form->createView(),
        ];

    }
}
