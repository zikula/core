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

namespace Zikula\ZAuthBundle\AuthenticationMethod;

use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersBundle\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\ZAuthBundle\Entity\AuthenticationMapping;
use Zikula\ZAuthBundle\Form\Type\RegistrationType;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthBundle\ZAuthConstant;

abstract class AbstractNativeAuthenticationMethod implements NonReEntrantAuthenticationMethodInterface
{
    public function __construct(
        private readonly AuthenticationMappingRepositoryInterface $mappingRepository,
        private readonly RequestStack $requestStack,
        private readonly ValidatorInterface $validator,
        protected readonly TranslatorInterface $translator,
        private readonly EncoderFactoryInterface $encoderFactory,
        private readonly bool $mailVerificationRequired
    ) {
    }

    public function getRegistrationFormClassName(): string
    {
        return RegistrationType::class;
    }

    public function getRegistrationTemplateName(): string
    {
        return '@ZikulaZAuth/Authentication/register.html.twig';
    }

    protected function authenticateByField(array $data, string $field = 'uname'): ?int
    {
        if (!isset($data[$field])) {
            return null;
        }

        $mapping = $this->getMapping($field, $data[$field]);
        if (!isset($mapping) || !$mapping->getPass()) {
            return null;
        }
        $passwordEncoder = $this->encoderFactory->getEncoder($mapping);

        if ($passwordEncoder->isPasswordValid($mapping->getPass(), $data['pass'], null)) {
            if ($passwordEncoder->needsRehash($mapping->getPass())) { // check to update hash to newer algo
                $this->updatePassword($mapping, $data['pass']);
            }

            return $mapping->getUid();
        }

        return null;
    }

    private function updatePassword(AuthenticationMapping $mapping, string $unHashedPassword)
    {
        $mapping->setPass($this->encoderFactory->getEncoder($mapping)->encodePassword($unHashedPassword, null));
        $this->mappingRepository->persistAndFlush($mapping);
    }

    /**
     * Get a AuthenticationMappingEntity if it exists. If not, check for existing UserEntity and
     * migrate data from UserEntity to AuthenticationMappingEntity and return that.
     *
     * @throws Exception
     */
    private function getMapping(string $field, string $value): ?AuthenticationMapping
    {
        /** @var AuthenticationMapping $mapping */
        $mapping = $this->mappingRepository->findOneBy([$field => $value]);
        if (
            isset($mapping)
            && (
                ('email' === $field && ZAuthConstant::AUTHENTICATION_METHOD_UNAME === $mapping->getMethod())
                || ('uname' === $field && ZAuthConstant::AUTHENTICATION_METHOD_EMAIL === $mapping->getMethod())
            )
        ) {
            // mapping exists but method is set to opposite. allow either if possible.
            $mapping->setMethod(ZAuthConstant::AUTHENTICATION_METHOD_EITHER);
            $errors = $this->validator->validate($mapping);
            if (count($errors) > 0) {
                // the error is probably only because of duplicate email... so....
                $request = $this->requestStack->getCurrentRequest();
                if ($request->hasSession() && ($session = $request->getSession())) {
                    $session->getFlashBag()->add(
                        'error',
                        'The email you are trying to authenticate with is in use by another user. You can only login by username.'
                    );
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
        $mapping = new AuthenticationMapping();
        $mapping->setUid($data['uid']);
        $mapping->setUname($data['uname']);
        $mapping->setEmail($data['email']);

        if (empty($data['pass'])) {
            $mapping->setPass('');
        } else {
            $mapping->setPass($this->encoderFactory->getEncoder($mapping)->encodePassword($data['pass'], null));
        }

        $mapping->setMethod($this->getAlias());

        $userMustVerify = $this->mailVerificationRequired;
        $request = $this->requestStack->getCurrentRequest();
        if ($request->hasSession() && ($session = $request->getSession())) {
            $userMustVerify = $session->has(ZAuthConstant::SESSION_EMAIL_VERIFICATION_STATE)
                ? 'Y' === $session->get(ZAuthConstant::SESSION_EMAIL_VERIFICATION_STATE)
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
