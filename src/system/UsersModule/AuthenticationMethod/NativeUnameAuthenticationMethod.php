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
use Zikula\UsersModule\Entity\Repository\UserRepository;
use Zikula\UsersModule\Entity\UserEntity;

class NativeUnameAuthenticationMethod implements NonReEntrantAuthenticationMethodInterface
{
    /**
     * @var UserRepository
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
     * @param UserRepository $userRepository
     * @param Session $session
     * @param TranslatorInterface $translator
     */
    public function __construct(UserRepository $userRepository, Session $session, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return 'Native Uname';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Allow a user to authenticate and login via Zikula\'s native user database';
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
    public function getLoginTemplateName()
    {
        return '@ZikulaUsersModule/Authentication/UnameLogin.html.twig';
    }

    public function getRegistrationFormClassName()
    {
        // TODO: Implement getRegistrationFormClassName() method.
    }

    public function getRegistrationTemplateName()
    {
        // TODO: Implement getRegistrationTemplateName() method.
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $data)
    {
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

        return null;
    }
}
