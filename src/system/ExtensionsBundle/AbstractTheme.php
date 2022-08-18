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

namespace Zikula\ExtensionsBundle;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Zikula\ExtensionsBundle\Api\VariableApi;
use Zikula\UsersBundle\Api\CurrentUserApi;

abstract class AbstractTheme extends AbstractExtension
{
    private array $config;

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

        $content = $twig->render('@ZikulaTheme/Default/maincontent.html.twig', [
            'classes' => $classes,
            'maincontent' => $response->getContent()
        ]);

        $content = $twig->render('@' . $this->getTemplateNamespace() . '/' . $template, ['maincontent' => $content]);
        $response = new Response($content);

        $isLoggedIn = $this->getContainer()->get(CurrentUserApi::class)->isLoggedIn();
        if ($isLoggedIn) {
            $response->headers->set('Cache-Control', 'nocache, no-store, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        }

        return $response;
    }

    protected function getTemplateNamespace(): string
    {
        $baseName = $this->name;
        $type = 'Bundle';
        if (str_ends_with($baseName, $type)) {
            $baseName = mb_substr($baseName, 0, -1 * mb_strlen($type));
        }

        return $baseName;
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
