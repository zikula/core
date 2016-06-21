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

use Symfony\Component\HttpFoundation\Session\Session;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class NativeUnameAuthenticationMethod implements NonReEntrantAuthenticationMethodInterface
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * NativeUnameAuthenticationMethod constructor.
     * @param UserRepositoryInterface $userRepository
     * @param Session $session
     * @param TranslatorInterface $translator
     */
    public function __construct(UserRepositoryInterface $userRepository, Session $session, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->session = $session;
        $this->translator = $translator;
    }

    public function getAlias()
    {
        return 'native_uname';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->__('Native Uname');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->__('Allow a user to authenticate and login via Zikula\'s native user database');
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginFormClassName()
    {
        return 'Zikula\UsersModule\Form\AuthenticationMethodType\UnameType';
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginTemplateName($type = 'page', $position = 'left')
    {
        if ($type == 'block') {
            if ($position == 'topnav') {
                return '@ZikulaUsersModule/Authentication/UnameLoginBlock.topnav.html.twig';
            }

            return '@ZikulaUsersModule/Authentication/UnameLoginBlock.html.twig';
        }

        return '@ZikulaUsersModule/Authentication/UnameLogin.html.twig';
    }

    public function getRegistrationFormClassName()
    {
        return 'Zikula\UsersModule\Form\Type\RegistrationType';
    }

    public function getRegistrationTemplateName()
    {
        return '@ZikulaUsersModule/Registration/register.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $data)
    {
        if (isset($data['uname'])) {
            $userEntity = $this->userRepository->findOneBy(['uname' => $data['uname']]);
            if ($userEntity) {
                if (\UserUtil::passwordsMatch($data['pass'], $userEntity->getPass())) { // @todo
                    return $userEntity->getUid();
                } else {
                    $this->session->getFlashBag()->add('error', $this->translator->__('Incorrect password'));
                }
            } else {
                $this->session->getFlashBag()->add('error', $this->translator->__f('User not found with uname %uname', ['%uname' => $data['uname']]));
            }
        }

        return null;
    }
}
