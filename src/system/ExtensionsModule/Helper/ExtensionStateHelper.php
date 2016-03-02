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

namespace Zikula\ExtensionsModule\Helper;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository;
use Zikula\ExtensionsModule\ExtensionEvents;

class ExtensionStateHelper
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var CacheClearer
     */
    private $cacheClearer;
    /**
     * @var ExtensionRepository
     */
    private $extensionRepository;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var ExtensionApi
     */
    private $extensionApi;

    /**
     * ExtensionStateHelper constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param CacheClearer $cacheClearer
     * @param ExtensionRepository $extensionRepository
     * @param TranslatorInterface $translator
     * @param ExtensionApi $extensionApi
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheClearer $cacheClearer,
        ExtensionRepository $extensionRepository,
        TranslatorInterface $translator,
        ExtensionApi $extensionApi
    ) {
        $this->dispatcher = $dispatcher;
        $this->cacheClearer = $cacheClearer;
        $this->extensionRepository = $extensionRepository;
        $this->translator = $translator;
        $this->extensionApi = $extensionApi;
    }

    /**
     * Set the state of a module.
     *
     * @param integer $id
     * @param integer $state
     *
     * @return boolean True if successful, false otherwise
     */
    public function updateState($id, $state)
    {
        /** @var ExtensionEntity $extension */
        $extension = $this->extensionRepository->find($id);
        $this->dispatcher->dispatch(ExtensionEvents::UPDATE_STATE, new GenericEvent($extension, ['state' => $state]));

        // Check valid state transition
        switch ($state) {
            case ExtensionApi::STATE_INACTIVE:
                $eventName = CoreEvents::MODULE_DISABLE;
                break;
            case ExtensionApi::STATE_ACTIVE:
                if ($extension->getState() === ExtensionApi::STATE_INACTIVE) {
                    // ACTIVE is used for freshly installed modules, so only register the transition if previously inactive.
                    $eventName = CoreEvents::MODULE_ENABLE;
                }
                break;
            case ExtensionApi::STATE_UPGRADED:
                if ($extension->getState() == ExtensionApi::STATE_UNINITIALISED) {
                    throw new \RuntimeException($this->translator->__('Error! Invalid module state transition.'));
                }
                break;
        }

        // change state
        $extension->setState($state);
        $this->extensionRepository->persistAndFlush($extension);

        // clear the cache before calling events
        $this->cacheClearer->clear('symfony');

        // state changed, so update the ModUtil::available-info for this module.
        // @todo refactor and remove ModUtil!
        \ModUtil::available($extension->getName(), true);

        if (isset($eventName)) {
            $moduleBundle = $this->extensionApi->getModuleInstanceOrNull($extension->getName());
            $event = new ModuleStateEvent($moduleBundle, ($moduleBundle === null) ? $extension->toArray() : null);
            $this->dispatcher->dispatch($eventName, $event);
        }

        return true;
    }
}
