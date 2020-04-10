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

namespace Zikula\ExtensionsModule;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Zikula\ExtensionsModule\Api\VariableApi;

abstract class AbstractTheme extends AbstractExtension
{
    /**
     * @var array
     */
    private $config;

    public function getNameType(): string
    {
        return 'Theme';
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Load the theme configuration from the config/theme.yaml file.
     */
    public function __construct()
    {
        $this->config = [];

        $configPath = $this->getConfigPath() . '/theme.yaml';
        if (!file_exists($configPath)) {
            return;
        }

        $this->config = Yaml::parse(file_get_contents($configPath));
        if (!isset($this->config['master'])) {
            throw new InvalidConfigurationException('Core-2.0 themes must have a defined master realm.');
        }
    }

    /**
     * Generate a response wrapped in the theme; wrap the main content in a unique div.
     */
    public function generateThemedResponse(
        string $realm,
        Response $response,
        string $moduleName = null
    ): Response {
        $template = $this->config[$realm]['page'];
        $classes = 'home' === $realm ? 'z-homepage' : '';
        $classes .= (empty($classes) ? '' : ' ') . (isset($moduleName) ? 'z-module-' . $moduleName : '');

        /* @var Environment $twig */
        $twig = $this->getContainer()->get('twig');

        $content = $twig->render('@ZikulaThemeModule/Default/maincontent.html.twig', [
            'classes' => $classes,
            'maincontent' => $response->getContent()
        ]);

        $content = $twig->render('@' . $this->name . '/' . $template, ['maincontent' => $content]);

        return new Response($content);
    }

    /**
     * Convert the block content to a theme-wrapped response.
     */
    public function generateThemedBlockContent(
        string $realm,
        string $positionName,
        string $blockContent,
        string $blockTitle
    ): string {
        if (isset($this->config[$realm]['block']['positions'][$positionName])) {
            $template = '@' . $this->name . '/' . $this->config[$realm]['block']['positions'][$positionName];
        } else {
            // block position not defined, provide a default template
            $template = '@ZikulaThemeModule/Default/block.html.twig';
        }

        return $this->getContainer()->get('twig')->render($template, [
            'title' => $blockTitle,
            'content' => $blockContent
        ]);
    }

    /**
     * Enclose themed block content in a unique div which is useful in applying styling.
     */
    public function wrapBlockContentWithUniqueDiv(
        string $content,
        string $positionName,
        string $blockType,
        int $blockId
    ): string {
        /* @var Environment $twig */
        $twig = $this->getContainer()->get('twig');

        return $twig->render('@ZikulaThemeModule/Default/blockwrapper.html.twig', [
            'position' => $positionName,
            'type' => $blockType,
            'bid' => $blockId,
            'content' => $content
        ]);
    }

    /**
     * Load the theme variables into the theme engine global vars.
     */
    public function loadThemeVars(): void
    {
        if ($this->getContainer()->has('zikula_core.common.theme.themevars')) {
            $this->getContainer()->get('zikula_core.common.theme.themevars')->replace($this->getThemeVars());
        }
    }

    /**
     * Get the theme variables from both the DB and the YAML file.
     */
    public function getThemeVars(): array
    {
        $variableApi = $this->container->get(VariableApi::class);
        $dbVars = $variableApi->getAll($this->name);
        if (empty($dbVars) && !is_array($dbVars)) {
            $dbVars = [];
        }
        $defaultVars = $this->getDefaultThemeVars();
        $combinedVars = array_merge($defaultVars, $dbVars);
        if (array_keys($dbVars) !== array_keys($combinedVars)) {
            // First load of file or vars have been added to the YAML file.
            $variableApi->setAll($this->name, $combinedVars);
        }

        return $combinedVars;
    }

    /**
     * Get the default values from variables.yaml.
     */
    public function getDefaultThemeVars(): array
    {
        $defaultVars = [];
        $themeVarsPath = $this->getConfigPath() . '/variables.yaml';
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
