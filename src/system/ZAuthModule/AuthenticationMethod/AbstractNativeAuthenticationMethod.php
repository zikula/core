<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\AuthenticationMethod;

use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Form\Type\RegistrationType;
use Zikula\ZAuthModule\ZAuthConstant;

abstract class AbstractNativeAuthenticationMethod implements NonReEntrantAuthenticationMethodInterface
{
    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $mappingRepository;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var PasswordApiInterface
     */
    private $passwordApi;

    public function __construct(
        AuthenticationMappingRepositoryInterface $mappingRepository,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        ValidatorInterface $validator,
        PasswordApiInterface $passwordApi
    ) {
        $this->mappingRepository = $mappingRepository;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->variableApi = $variableApi;
        $this->validator = $validator;
        $this->passwordApi = $passwordApi;
    }

    public function getRegistrationFormClassName(): string
    {
        return RegistrationType::class;
    }

    public function getRegistrationTemplateName(): string
    {
        return '@ZikulaZAuthModule/Authentication/register.html.twig';
    }

    protected function authenticateByField(array $data, string $field = 'uname'): ?int
    {
        if (!isset($data[$field])) {
            return null;
        }

        $mapping = $this->getMapping($field, $data[$field]);
        if ($mapping && $this->passwordApi->passwordsMatch($data['pass'], $mapping->getPass())) {
            // is this the place to update the hash method? #2842
            return $mapping->getUid();
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->getFlashBag()->add('error', $this->translator->trans('Login failed.'));
        }

        return null;
    }

    /**
     * Get a AuthenticationMappingEntity if it exists. If not, check for existing UserEntity and
     * migrate data from UserEntity to AuthenticationMappingEntity and return that.
     *
     * @throws Exception
     */
    private function getMapping(string $field, string $value): ?AuthenticationMappingEntity
    {
        /** @var AuthenticationMappingEntity $mapping */
        $mapping = $this->mappingRepository->findOneBy([$field => $value]);
        if (isset($mapping) && (
            ('email' === $field && ZAuthConstant::AUTHENTICATION_METHOD_UNAME === $mapping->getMethod())
            || ('uname' === $field && ZAuthConstant::AUTHENTICATION_METHOD_EMAIL === $mapping->getMethod()))
        ) {
            // mapping exists but method is set to opposite. allow either if possible.
            $mapping->setMethod(ZAuthConstant::AUTHENTICATION_METHOD_EITHER);
            $errors = $this->validator->validate($mapping);
            if (count($errors) > 0) {
                // the error is probably only because of duplicate email... so....
                $request = $this->requestStack->getCurrentRequest();
                if ($request->hasSession() && ($session = $request->getSession())) {
                    $session->getFlashBag()->add('error', $this->translator->trans('The email you are trying to authenticate with is in use by another user. You can only login by username.'));
                }
                $mapping = null;
            } else {
                $this->mappingRepository->persistAndFlush($mapping);
            }
        }

        return $mapping;
    }

    public function register(array $data = []): bool
    {
        $mapping = new AuthenticationMappingEntity();
        $mapping->setUid($data['uid']);
        $mapping->setUname($data['uname']);
        $mapping->setEmail($data['email']);

        if (empty($data['pass'])) {
            $mapping->setPass('');
        } else {
            $mapping->setPass($this->passwordApi->getHashedPassword($data['pass']));
        }

        $mapping->setMethod($this->getAlias());

        $userMustVerify = $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED, ZAuthConstant::DEFAULT_EMAIL_VERIFICATION_REQUIRED);
        $request = $this->requestStack->getCurrentRequest();
        if ($request->hasSession() && ($session = $request->getSession())) {
            $userMustVerify = $session->has(ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED)
                ? 'Y' === $session->get(ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED)
                : $userMustVerify;
        }

        $mapping->setVerifiedEmail(!$userMustVerify);
        $errors = $this->validator->validate($mapping);
        if ($request->hasSession() && ($session = $request->getSession()) && 0 < count($errors)) {
            foreach ($errors as $error) {
                $session->getFlashBag()->add('error', $error->getMessage());
            }

            return false;
        }
        $this->mappingRepository->persistAndFlush($mapping);

        return true;
    }
}
