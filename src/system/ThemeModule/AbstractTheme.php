<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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

    private $isTwigBased = false;

    public function getNameType()
    {
        return 'Theme';
    }

    public function getServiceIds()
    {
        return [];
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * load the theme configuration from the config/theme.yml file
     */
    public function __construct()
    {
        $configPath = $this->getConfigPath() . '/theme.yml';
        if (file_exists($configPath)) {
            $this->config = Yaml::parse(file_get_contents($configPath));
            if (!isset($this->config['master'])) {
                throw new InvalidConfigurationException('Core-2.0 themes must have a defined master realm.');
            }
            $this->isTwigBased = true;
        }
    }

    /**
     * generate a response wrapped in the theme
     *   wrap the maincontent in a unique div.
     * @param string $realm
     * @param Response $response
     * @param string $moduleName
     * @return Response
     */
    public function generateThemedResponse($realm, Response $response, $moduleName = null)
    {
        $template = $this->config[$realm]['page'];
        $content = '<div id="z-maincontent" class="'
            . ($realm == 'home' ? 'z-homepage' : '')
            . (isset($moduleName) ? ' z-module-' . strtolower($moduleName) : '') . '">'
            . $response->getContent()
            . '</div>';
        $response->setContent($content);

        return $this->getContainer()->get('templating')->renderResponse($this->name . ':' . $template, ['maincontent' => $response->getContent()]);
    }

    /**
     * convert the block content to a theme-wrapped Response
     * @param string $realm
     * @param $positionName
     * @param string $blockContent
     * @param $blockTitle
     * @return string
     */
    public function generateThemedBlockContent($realm, $blockType, $bid, $positionName, $blockContent, $blockTitle)
    {
        if (isset($this->config[$realm]['block']['positions'][$positionName])) {
            $template = $this->name . ':' . $this->config[$realm]['block']['positions'][$positionName];
        } else {
            // block position not defined, provide a default template
            $template = 'CoreBundle:Default:block.html.twig';
        }

        $templateParameters = [
            'btype' => strtolower($blockType),
            'bid' => $bid,
            'position' => strtolower($positionName),
            'title' => $blockTitle,
            'content' => $blockContent
        ];
        // @todo add collapsable block code see \BlockUtil::themeBlock
        // @todo including check for `isCollapsed` like \BlockUtil::checkUserBlock

        return $this->getContainer()->get('templating')->render($template, $templateParameters);
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
     * @return array|string
     */
    public function getThemeVars()
    {
        $dbVars = \ModUtil::getVar($this->name);
        if (empty($dbVars) && !is_array($dbVars)) {
            $dbVars = [];
        }
        $defaultVars = $this->getDefaultThemeVars();
        $combinedVars = array_merge($defaultVars, $dbVars);
        if (array_keys($dbVars) != array_keys($combinedVars)) {
            // First load of file or vars have been added to the .yml file.
            \ModUtil::setVars($this->name, $combinedVars);
        }

        return $combinedVars;
    }

    /**
     * Get the default values from variables.yml.
     * @return array
     */
    public function getDefaultThemeVars()
    {
        $defaultVars = [];
        $themeVarsPath = $this->getConfigPath() . '/variables.yml';
        if (file_exists($themeVarsPath)) {
            if ($this->getContainer()) {
                $yamlVars = Yaml::parse(file_get_contents($themeVarsPath));
                foreach ($yamlVars as $name => $definition) {
                    $defaultVars[$name] = $definition['default_value'];
                }
            }
        }

        return $defaultVars;
    }

    /**
     * Is theme twig (Core-2.0) based?
     * @deprecated
     * @return bool
     */
    public function isTwigBased()
    {
        return $this->isTwigBased;
    }
}
