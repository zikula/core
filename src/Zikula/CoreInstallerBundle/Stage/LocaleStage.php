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

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Configurator;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class LocaleStage implements StageInterface, FormHandlerInterface
{
    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var array
     */
    private $installedLocales;

    /**
     * @var string
     */
    private $matchedLocale;

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(
        LocaleApiInterface $localeApi,
        TranslatorInterface $translator,
        CacheClearer $cacheClearer,
        string $projectDir
    ) {
        $this->localeApi = $localeApi;
        $this->translator = $translator;
        $this->cacheClearer = $cacheClearer;
        $this->installedLocales = $localeApi->getSupportedLocales();
        $this->matchedLocale = $localeApi->getBrowserLocale();
        $this->projectDir = $projectDir;
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
            'choices' => $this->localeApi->getSupportedLocaleNames(),
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
        $configurator = new Configurator($this->projectDir);
        $configurator->loadPackages('zikula_settings');
        $configurator->set('zikula_settings', 'locale', $data['locale']);
        $configurator->write();
        $this->cacheClearer->clear('symfony.config');
    }
}
