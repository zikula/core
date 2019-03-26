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

namespace Zikula\ThemeModule;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Zikula\Core\AbstractBundle;

abstract class AbstractTheme extends AbstractBundle
{
    private $config;

    public function getNameType()
    {
        return 'Theme';
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Load the theme configuration from the config/theme.yml file.
     */
    public function __construct()
    {
        $configPath = $this->getConfigPath() . '/theme.yml';
        if (!file_exists($configPath)) {
            return;
        }

        $this->config = Yaml::parse(file_get_contents($configPath));
        if (!isset($this->config['master'])) {
            throw new InvalidConfigurationException('Core-2.0 themes must have a defined master realm.');
        }
    }

    /**
     * Generate a response wrapped in the theme
     *   wrap the maincontent in a unique div.
     *
     * @param string $realm
     * @param Response $response
     * @param string $moduleName
     *
     * @return Response
     */
    public function generateThemedResponse($realm, Response $response, $moduleName = null)
    {
        $template = $this->config[$realm]['page'];
        $classes = 'home' === $realm ? 'z-homepage' : '';
        $classes .= (empty($classes) ? '' : ' ') . (isset($moduleName) ? 'z-module-' . $moduleName : '');

        /* @var Twig\Environment */
        $twig = $this->getContainer()->get('twig');

        $content = $twig->render('ZikulaThemeModule:Default:maincontent.html.twig', [
            'classes' => $classes,
            'maincontent' => $response->getContent()
        ]);

        $content = $twig->render($this->name . ':' . $template, ['maincontent' => $content]);

        return new Response($content);
    }

    /**
     * Convert the block content to a theme-wrapped response.
     *
     * @param string $realm
     * @param $positionName
     * @param string $blockContent
     * @param $blockTitle
     *
     * @return string
     */
    public function generateThemedBlockContent($realm, $positionName, $blockContent, $blockTitle)
    {
        if (isset($this->config[$realm]['block']['positions'][$positionName])) {
            $template = $this->name . ':' . $this->config[$realm]['block']['positions'][$positionName];
        } else {
            // block position not defined, provide a default template
            $template = 'ZikulaThemeModule:Default:block.html.twig';
        }

        $templateParameters = [
            'title' => $blockTitle,
            'content' => $blockContent
        ];

        return $this->getContainer()->get('twig')->render($template, $templateParameters);
    }

    /**
     * Enclose themed block content in a unique div which is useful in applying styling.
     *
     * @param string $content
     * @param string $positionName
     * @param string $blockType
     * @param integer $bid
     *
     * @return string
     */
    public function wrapBlockContentWithUniqueDiv($content, $positionName, $blockType, $bid)
    {
        $templateParams = [
            'position' => $positionName,
            'type' => $blockType,
            'bid' => $bid,
            'content' => $content
        ];

        return $this->getContainer()->get('twig')->render('ZikulaThemeModule:Default:blockwrapper.html.twig', $templateParams);
    }

    /**
     * load the themevars into the themeEngine global vars
     */
    public function loadThemeVars()
    {
        if ($this->getContainer()->has('zikula_core.common.theme.themevars')) {
            $this->getContainer()->get('zikula_core.common.theme.themevars')->replace($this->getThemeVars());
        }
    }

    /**
     * Get the theme variables from both the DB and the .yml file.
     *
     * @return array|string
     */
    public function getThemeVars()
    {
        $variableApi = $this->container->get('Zikula\ExtensionsModule\Api\VariableApi');
        $dbVars = $variableApi->getAll($this->name);
        if (empty($dbVars) && !is_array($dbVars)) {
            $dbVars = [];
        }
        $defaultVars = $this->getDefaultThemeVars();
        $combinedVars = array_merge($defaultVars, $dbVars);
        if (array_keys($dbVars) !== array_keys($combinedVars)) {
            // First load of file or vars have been added to the .yml file.
            $variableApi->setAll($this->name, $combinedVars);
        }

        return $combinedVars;
    }

    /**
     * Get the default values from variables.yml.
     *
     * @return array
     */
    public function getDefaultThemeVars()
    {
        $defaultVars = [];
        $themeVarsPath = $this->getConfigPath() . '/variables.yml';
        if (!file_exists($themeVarsPath)) {
            return $defaultVars;
        }

        /*if (!$this->getContainer()) {
            return $defaultVars;
        }*/

        $yamlVars = Yaml::parse(file_get_contents($themeVarsPath));
        if (!is_array($yamlVars)) {
            $yamlVars = [];
        }
        foreach ($yamlVars as $name => $definition) {
            $defaultVars[$name] = $definition['default_value'];
        }

        return $defaultVars;
    }
}
