<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Token;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\ExtensionsModule\Api\VariableApi;

class CsrfTokenHandler
{
    /**
     * @var Generator
     */
    private $generator;
    /**
     * @var Validator
     */
    private $validator;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var VariableApi
     */
    private $variableApi;
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * CsrfHandler constructor.
     * @param Generator $generator
     * @param Validator $validator
     * @param RequestStack $requestStack
     * @param VariableApi $variableApi
     * @param SessionInterface $session
     */
    public function __construct(Generator $generator, Validator $validator, RequestStack $requestStack, VariableApi $variableApi, SessionInterface $session)
    {
        $this->generator = $generator;
        $this->validator = $validator;
        $this->requestStack = $requestStack;
        $this->variableApi = $variableApi;
        $this->session = $session;
    }

    /**
     * Validate a Csrf token.
     *
     * @param string $token The token, if not set, will pull from $_POST['csrftoken'].
     * @param bool $invalidateSessionOnFailure
     */
    public function validate($token = null, $invalidateSessionOnFailure = false)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (is_null($token)) {
            $token = $request->request->get('csrftoken', false);
        }

        if ($this->variableApi->get(VariableApi::CONFIG, 'sessioncsrftokenonetime') && $this->validator->validate($token, false, false)) {
            return true;
        }

        if ($this->validator->validate($token)) {
            return true;
        }

        // validation failed
        if ($invalidateSessionOnFailure) {
            $this->session->invalidate();
            $this->session->set('session_expire', true);
        }
        throw new AccessDeniedException('Error! Something went wrong: security token validation failed. Go to the <a href="' . $request->getBaseUrl() . '">homepage</a>.');
    }

    /**
     * Generate a Csrf token.
     *
     * @param boolean $forceUnique Force a unique token regardless of system settings.
     *
     * @return string
     */
    public function generate($forceUnique = false)
    {
        if (!$forceUnique && $this->variableApi->get(VariableApi::CONFIG, 'sessioncsrftokenonetime')) {
            $storage = $this->generator->getStorage();
            $tokenId = $this->session->get('sessioncsrftokenid');
            $data = $storage->get($tokenId);
            if (!$data) {
                $this->generator->generate($this->generator->uniqueId(), time());
                $this->generator->save();
                $this->session->set('sessioncsrftokenid', $this->generator->getId());

                return $this->generator->getToken();
            }

            return $data['token'];
        }

        $this->generator->generate($this->generator->uniqueId(), time());
        $this->generator->save();

        return $this->generator->getToken();
    }
}
