<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\HookBundle\Api;

use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;

class HookApi
{
    /**
     * Provider capability key.
     * @deprecated
     * @see CapabilityApiInterface::HOOK_PROVIDER
     */
    const PROVIDER_TYPE = 'hook_provider';

    /**
     * Subscriber capability key.
     * @deprecated
     * @see CapabilityApiInterface::HOOK_SUBSCRIBER
     */
    const SUBSCRIBER_TYPE = 'hook_subscriber';

    /**
     * Allow to provide to self.
     * @deprecated
     * @see CapabilityApiInterface::HOOK_SUBSCRIBER_OWN
     */
    const SELF_TYPE = 'subscribe_own';
    /**
     * @var \Zikula\Common\Translator\Translator
     */
    private $translator;

    /**
     * HookApi constructor.
     * @param $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Factory class to create instance of HookContainer class defined in MetaData::capabilities.
     * @param MetaData $metaData
     * @param null $requestedHookType
     * @return null|\Zikula\Component\HookDispatcher\AbstractContainer
     */
    public function getHookContainerInstance(MetaData $metaData, $requestedHookType = null)
    {
        foreach ([self::SUBSCRIBER_TYPE, self::PROVIDER_TYPE] as $type) {
            if (isset($metaData->getCapabilities()[$type]['class'])
                && (!isset($requestedHookType) || $type == $requestedHookType)) {
                $hookContainerClassName = $metaData->getCapabilities()[$type]['class'];
                $reflection = new \ReflectionClass($hookContainerClassName);
                if ($reflection->isSubclassOf('Zikula\Component\HookDispatcher\AbstractContainer')) {
                    return new $hookContainerClassName($this->translator);
                }
            }
        }

        return null;
    }
}
