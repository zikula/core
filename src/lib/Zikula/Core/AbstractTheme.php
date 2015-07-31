<?php

namespace Zikula\Core;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

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
        return array();
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
            $this->config = Yaml::parse($configPath);
            if (!isset($this->config['master'])) {
                throw new InvalidConfigurationException('Core-2.0 themes must have a defined master realm.');
            }
            $this->isTwigBased = true;
        }
    }

    /**
     * generate a response wrapped in the theme
     * @param Response $response
     * @return Response
     */
    public function generateThemedResponse(Response $response)
    {
        // @todo determine proper template? and location
        // @todo NOTE: 'pagetype' is temporary var in the template

        $realm = $this->getContainer()->get('zikula_core.common.theme_engine')->getRealm();
        $template = $this->config[$realm]['page'];

        return $this->getContainer()->get('templating')->renderResponse($this->name . ':' . $template, array('maincontent' => $response->getContent(), 'pagetype' => 'admin'));
    }

    /**
     * convert the block content to a theme-wrapped Response
     * @param array $block
     * @return string
     */
    public function generateThemedBlock(array $block)
    {
        $realm = $this->getContainer()->get('zikula_core.common.theme_engine')->getRealm();
        $template = $this->config[$realm]['block']['positions'][$block['position']];

        return $this->getContainer()->get('templating')->render($this->name . ':' . $template, $block); // @todo renderView? renderResponse?
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
