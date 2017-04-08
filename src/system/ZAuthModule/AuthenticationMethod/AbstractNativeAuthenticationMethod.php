<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\AuthenticationMethod;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\ZAuthModule\Api\PasswordApi;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Helper\MigrationHelper;
use Zikula\ZAuthModule\ZAuthConstant;

abstract class AbstractNativeAuthenticationMethod implements NonReEntrantAuthenticationMethodInterface
{
    /**
     * @deprecated
     * @var MigrationHelper
     */
    private $migrationHelper;

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
    protected $translator;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var PasswordApi
     */
    private $passwordApi;

    /**
     * AbstractNativeAuthenticationMethod constructor.
     * @param MigrationHelper $migrationHelper @deprecated
     * @param AuthenticationMappingRepositoryInterface $mappingRepository
     * @param Session $session
     * @param TranslatorInterface $translator
     * @param VariableApi $variableApi
     * @param ValidatorInterface $validator
     * @param PasswordApi $passwordApi
     */
    public function __construct(
        MigrationHelper $migrationHelper, // @deprecated
        AuthenticationMappingRepositoryInterface $mappingRepository,
        Session $session,
        TranslatorInterface $translator,
        VariableApi $variableApi,
        ValidatorInterface $validator,
        PasswordApi $passwordApi
    ) {
        $this->migrationHelper = $migrationHelper; // @deprecated
        $this->mappingRepository = $mappingRepository;
        $this->session = $session;
        $this->translator = $translator;
        $this->variableApi = $variableApi;
        $this->validator = $validator;
        $this->passwordApi = $passwordApi;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegistrationFormClassName()
    {
        return 'Zikula\ZAuthModule\Form\Type\RegistrationType';
    }

    /**
     * {@inheritdoc}
     */
    public function getRegistrationTemplateName()
    {
        return 'ZikulaZAuthModule:Authentication:register.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateByField(array $data, $field = 'uname')
    {
        if (isset($data[$field])) {
            $mapping = $this->getMapping($field, $data[$field]);
            if ($mapping) {
                if ($this->passwordApi->passwordsMatch($data['pass'], $mapping->getPass())) {
                    // @todo is this the place to update the hash method?
                    return $mapping->getUid();
                } else {
                    $this->session->getFlashBag()->add('error', $this->translator->__('Incorrect password'));
                }
            } else {
                $this->session->getFlashBag()->add('error', $this->translator->__f('User not found with %field %value', ['%field' => $field, '%value' => $data[$field]]));
            }
        }

        return null;
    }

    /**
     * Get a AuthenticationMappingEntity if it exists. If not, check for existing UserEntity and
     * migrate data from UserEntity to AuthenticationMappingEntity and return that.
     * @todo The migration from UserEntity parts of this method must be removed at Core-2.0
     * @param string $field the field to perform lookup by
     * @param string $value the value of that field
     * @return null|AuthenticationMappingEntity
     * @throws \Exception
     */
    private function getMapping($field, $value)
    {
        $mapping = $this->mappingRepository->findOneBy([$field => $value]);
        if (!isset($mapping)) {
            return $this->migrationHelper->createMappingFromUserCriteria([$field => $value]); // @deprecated
        } elseif (($field == 'email' && ZAuthConstant::AUTHENTICATION_METHOD_UNAME == $mapping->getMethod())
            || ($field == 'uname' && ZAuthConstant::AUTHENTICATION_METHOD_EMAIL == $mapping->getMethod())) {
            // mapping exists but method is set to opposite. allow either if possible.
            $mapping->setMethod(ZAuthConstant::AUTHENTICATION_METHOD_EITHER);
            $errors = $this->validator->validate($mapping);
            if (count($errors) > 0) {
                // the error is probably only because of duplicate email... so....
                $this->session->getFlashBag()->add('error', $this->translator->__('The email you are trying to authenticate with is in use by another user. You can only login by username.'));
                $mapping = null;
            } else {
                $this->mappingRepository->persistAndFlush($mapping);
            }
        }

        return $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function register(array $data)
    {
        $mapping = new AuthenticationMappingEntity();
        $mapping->setUid($data['uid']);
        $mapping->setUname($data['uname']);
        $mapping->setEmail($data['email']);
        $mapping->setPass($this->passwordApi->getHashedPassword($data['pass']));
        $mapping->setMethod($this->getAlias());
        $requireVerifiedEmail = $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED, ZAuthConstant::DEFAULT_EMAIL_VERIFICATION_REQUIRED);
        $mapping->setVerifiedEmail(!$requireVerifiedEmail);
        $errors = $this->validator->validate($mapping);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->session->getFlashBag()->add('error', $error->getMessage());
            }

            return false;
        }
        $this->mappingRepository->persistAndFlush($mapping);

        return true;
    }
}
