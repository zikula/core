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

namespace Zikula\UsersBundle\Collector;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\SettingsBundle\SettingsConstant;
use Zikula\UsersBundle\MessageBundle\IdentityMessageBundle;
use Zikula\UsersBundle\MessageBundle\MessageBundleInterface;

class MessageBundleCollector
{
    /**
     * @var MessageBundleInterface[] e.g. [<BundleName> => <ServiceObject>]
     */
    private array $messageBundles = [];

    private string $currentMessageBundleName;

    public function __construct(
        VariableApiInterface $variableApi,
        #[TaggedIterator('zikula.message_bundle')]
        iterable $bundles
    ) {
        $this->currentMessageBundleName = $variableApi->getSystemVar(SettingsConstant::SYSTEM_VAR_MESSAGE_BUNDLE, '');
        foreach ($bundles as $bundle) {
            $this->add($bundle);
        }
    }

    /**
     * Add a service to the collection.
     */
    public function add(/* MessageBundleInterface */ $service): void
    {
        $bundleName = $service->getBundleName();
        if ('ZikulaUsersBundle' === $bundleName) {
            return;
        }
        if (isset($this->messageBundles[$bundleName])) {
            throw new InvalidArgumentException('Attempting to register a message bundle with a duplicate bundle name. (' . $bundleName . ')');
        }
        $this->messageBundles[$bundleName] = $service;
    }

    /**
     * Get a MessageBundleInterface from the collection by bundle name.
     */
    public function get(string $bundleName): ?MessageBundleInterface
    {
        return $this->messageBundles[$bundleName] ?? null;
    }

    /**
     * Get all the bundles in the collection.
     *
     * @return MessageBundleInterface[]
     */
    public function getAll(): iterable
    {
        return $this->messageBundles;
    }

    /**
     * Get an array of service aliases.
     */
    public function getKeys(): array
    {
        return array_keys($this->messageBundles);
    }

    public function getSelected(): MessageBundleInterface
    {
        if (!empty($this->currentMessageBundleName) && isset($this->messageBundles[$this->currentMessageBundleName])) {
            return $this->messageBundles[$this->currentMessageBundleName];
        }

        return new IdentityMessageBundle();
    }
}
