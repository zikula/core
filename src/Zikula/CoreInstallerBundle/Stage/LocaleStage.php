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

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\SettingsModule\Api\LocaleApi;

class LocaleStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    /**
     * @var YamlDumper
     */
    private $yamlManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $installedLocales;

    /**
     * @var string
     */
    private $matchedLocale;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getProjectDir() . '/config', 'services_custom.yaml', 'services.yaml');
        $this->installedLocales = $container->get(LocaleApi::class)->getSupportedLocales();
        $this->matchedLocale = $container->get(LocaleApi::class)->getBrowserLocale();
    }

    public function getName(): string
    {
        return 'locale';
    }

    public function getFormType(): string
    {
        return LocaleType::class;
    }

    public function getFormOptions(): array
    {
        return [
            'choices' => $this->container->get(LocaleApi::class)->getSupportedLocaleNames(),
            'choice_loader' => null,
            'choice' => $this->matchedLocale
        ];
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Install/locale.html.twig';
    }

    public function isNecessary(): bool
    {
        if (1 === count($this->installedLocales)) {
            $defaultLocale = array_values($this->installedLocales)[0];
            $this->writeParams(['locale' => $defaultLocale]);

            return false;
        }

        return true;
    }

    public function getTemplateParams(): array
    {
        return [];
    }

    public function handleFormResult(FormInterface $form): bool
    {
        $data = $form->getData();
        $this->writeParams($data);

        return true;
    }

    private function writeParams($data = []): void
    {
        $params = array_merge($this->yamlManager->getParameters(), $data);
        try {
            $this->yamlManager->setParameters($params);
        } catch (IOException $e) {
            throw new AbortStageException($this->container->get('translator')->trans('Cannot write parameters to %fileName% file.', ['%fileName%' => 'services_custom.yaml']));
        }
        // clear container cache
        $this->container->get(CacheClearer::class)->clear('symfony.config');
    }
}
