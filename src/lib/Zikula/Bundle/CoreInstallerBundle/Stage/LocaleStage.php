<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

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
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() . '/config', 'custom_parameters.yml', 'parameters.yml');
        $this->installedLocales = $container->get('zikula_settings_module.locale_api')->getSupportedLocales();
        $this->matchedLocale = $container->get('zikula_settings_module.locale_api')->getBrowserLocale();
    }

    public function getName()
    {
        return 'locale';
    }

    public function getFormType()
    {
        return 'Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType';
    }

    public function getFormOptions()
    {
        return [
            'choices' => $this->container->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(),
            'choice' => $this->matchedLocale,
            'translator' => $this->container->get('translator.default')
        ];
    }

    public function getTemplateName()
    {
        return 'ZikulaCoreInstallerBundle:Install:locale.html.twig';
    }

    public function isNecessary()
    {
        if (count($this->installedLocales) == 1) {
            $defaultLocale = array_values($this->installedLocales)[0];
            $this->writeParams(['locale' => $defaultLocale]);

            return false;
        }

        return true;
    }

    public function getTemplateParams()
    {
        return [];
    }

    public function handleFormResult(FormInterface $form)
    {
        $data = $form->getData();
        $this->writeParams($data);
    }

    private function writeParams($data = [])
    {
        $params = array_merge($this->yamlManager->getParameters(), $data);
        try {
            $this->yamlManager->setParameters($params);
        } catch (IOException $e) {
            throw new AbortStageException($this->container->get('translator.default')->__f('Cannot write parameters to %s file.', ['%s' => 'custom_parameters.yml']));
        }
        // clear container cache
        $this->container->get('zikula.cache_clearer')->clear('symfony.config');
    }
}
