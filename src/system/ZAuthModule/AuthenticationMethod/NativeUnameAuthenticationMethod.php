<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\AuthenticationMethod;

use Symfony\Component\HttpFoundation\Session\Session;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\ZAuthModule\ZAuthConstant;

class NativeUnameAuthenticationMethod implements NonReEntrantAuthenticationMethodInterface
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $mappingRepository;

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
     * @param AuthenticationMappingRepositoryInterface $mappingRepository
     * @param Session $session
     * @param TranslatorInterface $translator
     */
    public function __construct(UserRepositoryInterface $userRepository, AuthenticationMappingRepositoryInterface $mappingRepository, Session $session, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->mappingRepository = $mappingRepository;
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
        return 'Zikula\ZAuthModule\Form\Type\UnameLoginType';
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginTemplateName($type = 'page', $position = 'left')
    {
        if ($type == 'block') {
            if ($position == 'topnav') {
                return '@ZikulaZAuthModule/Authentication/UnameLoginBlock.topnav.html.twig';
            }

            return '@ZikulaZAuthModule/Authentication/UnameLoginBlock.html.twig';
        }

        return '@ZikulaZAuthModule/Authentication/UnameLogin.html.twig';
    }

    public function getRegistrationFormClassName()
    {
        return 'Zikula\ZAuthModule\Form\Type\RegistrationType';
    }

    public function getRegistrationTemplateName()
    {
        return '@ZikulaZAuthModule/Authentication/register.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $data)
    {
        if (isset($data['uname'])) {
            $mapping = $this->getMapping($data['uname']);
            if ($mapping) {
                if (\UserUtil::passwordsMatch($data['pass'], $mapping->getPass())) { // @todo
                    // @todo is this the place to update the hash method?
                    return $mapping->getUid();
                } else {
                    $this->session->getFlashBag()->add('error', $this->translator->__('Incorrect password'));
                }
            } else {
                $this->session->getFlashBag()->add('error', $this->translator->__f('User not found with uname %uname', ['%uname' => $data['uname']]));
            }
        }

        return null;
    }

    /**
     * Get a AuthenticationMappingEntity if it exists. If not, check for existing UserEntity and
     * migrate data from UserEntity to AuthenticationMappingEntity and return that.
     * If mapping exists
     * @param string $uname
     * @return AuthenticationMappingEntity|null
     */
    private function getMapping($uname)
    {
        $mapping = $this->mappingRepository->findOneBy(['uname' => $uname]);
        if (!isset($mapping)) {
            $userEntity = $this->userRepository->findOneBy(['uname' => $uname]);
            if ($userEntity) {
                // create new mapping
                $mapping = new AuthenticationMappingEntity();
                $mapping->setUid($userEntity->getUid());
                $mapping->setUname($userEntity->getUname());
                $mapping->setEmail($userEntity->getEmail());
                $mapping->setPass($userEntity->getPass()); // salted and hashed
                $mapping->setPassreminder($userEntity->getPassreminder());
                $mapping->setMethod($this->getAlias());
                // @todo validate the new entity? check for duplicates, etc.
                $this->mappingRepository->persistAndFlush($mapping);
                // remove data from UserEntity
                $userEntity->setPass('');
                $userEntity->setPassreminder('');
                $this->userRepository->persistAndFlush($userEntity);

                return $mapping;
            }
        } elseif ('native_email' == $mapping->getMethod()) {
            $mapping->setMethod(ZAuthConstant::AUTHENTICATION_METHOD_EITHER);
            $this->mappingRepository->persistAndFlush($mapping);
        }

        return $mapping;
    }

    public function register(array $data)
    {
        $mapping = new AuthenticationMappingEntity();
        $mapping->setUid($data['uid']);
        $mapping->setUname($data['user']['uname']);
        $mapping->setEmail($data['user']['email']);
        $mapping->setPass(\UserUtil::getHashedPassword($data['pass'])); // @todo salted and hashed
        if (isset($data['passreminder'])) {
            $mapping->setPassreminder($data['passreminder']);
        }
        $mapping->setMethod($this->getAlias());
        // @todo validate the new entity? check for duplicates, etc.
        $this->mappingRepository->persistAndFlush($mapping);
    }
}
