<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Generates a Controller inside a module.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ControllerGenerator extends Generator
{
    private $filesystem;
    private $skeletonDir;

    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->skeletonDir = $skeletonDir;
    }

    public function generate(BundleInterface $module, $controller, $routeFormat, $templateFormat, array $actions = array())
    {
        $dir = $module->getPath();
        $controllerFile = $dir.'/Controller/'.$controller.'Controller.php';
        if (file_exists($controllerFile)) {
            throw new \RuntimeException(sprintf('Controller "%s" already exists', $controller));
        }

        $parameters = array(
            'namespace'  => $module->getNamespace(),
            'module'     => $module->getName(),
            'format'     => array(
                'routing'    => $routeFormat,
                'templating' => $templateFormat,
            ),
            'controller' => $controller,
        );

        foreach ($actions as $i => $action) {
            // get the actioname without the sufix Action (for the template logical name)
            $actions[$i]['basename'] = $basename = substr($action['name'], 0, -6);
            $params = $parameters;
            $params['action'] = $actions[$i];

            // create a template
            $template = $actions[$i]['template'];
            if ('default' == $template) {
                $template = $module->getName().':'.$controller.':'.substr($action['name'], 0, -6).'.html.'.$templateFormat;
            }

            if ('twig' == $templateFormat) {
                $this->renderFile($this->skeletonDir, 'Template.html.twig', $dir.'/Resources/views/'.$this->parseTemplatePath($template), $params);
            } else {
                $this->renderFile($this->skeletonDir, 'Template.html.php', $dir.'/Resources/views/'.$this->parseTemplatePath($template), $params);
            }

            $this->generateRouting($module, $controller, $actions[$i], $routeFormat);
        }

        $parameters['actions'] = $actions;

        $this->renderFile($this->skeletonDir, 'Controller.php.twig', $controllerFile, $parameters);
        $this->renderFile($this->skeletonDir, 'ControllerTest.php.twig', $dir.'/Tests/Controller/'.$controller.'ControllerTest.php', $parameters);
    }

    public function generateRouting(BundleInterface $module, $controller, array $action, $format)
    {
        // annotation is generated in the templates
        if ('annotation' == $format) {
            return true;
        }

        $file = $module->getPath().'/Resources/config/routing.'.$format;
        if (file_exists($file)) {
            $content = file_get_contents($file);
        } elseif (!is_dir($dir = $module->getPath().'/Resources/config')) {
            mkdir($dir);
        }

        $controller = $module->getName().':'.$controller.':'.$action['basename'];
        $name = strtolower(preg_replace('/([A-Z])/', '_\\1', $action['basename']));

        if ('yml' == $format) {
            // yaml
            if (!isset($content)) {
                $content = '';
            }

            $content .= sprintf(
                "\n%s:\n    pattern: %s\n    defaults: { _controller: %s }\n",
                $name,
                $action['route'],
                $controller
            );
        } elseif ('xml' == $format) {
            // xml
            if (!isset($content)) {
                // new file
                $content = <<<EOT
<?xml version="1.0" encoding="UTF-8" ?>
<routes xmlns="http://symfony.com/schema/routing"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">
</routes>
EOT;
            }

            $sxe = simplexml_load_string($content);

            $route = $sxe->addChild('route');
            $route->addAttribute('id', $name);
            $route->addAttribute('pattern', $action['route']);

            $default = $route->addChild('default', $controller);
            $default->addAttribute('key', '_controller');

            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($sxe->asXML());
            $content = $dom->saveXML();
        } elseif ('php' == $format) {
            // php
            if (isset($content)) {
                // edit current file
                $pointer = strpos($content, 'return');
                if (!preg_match('/(\$[^ ]*).*?new RouteCollection\(\)/', $content, $collection) || false === $pointer) {
                    throw new \RunTimeException('Routing.php file is not correct, please initialize RouteCollection.');
                }

                $content = substr($content, 0, $pointer);
                $content .= sprintf("%s->add('%s', new Route('%s', array(", $collection[1], $name, $action['route']);
                $content .= sprintf("\n    '_controller' => '%s',", $controller);
                $content .= "\n)));\n\nreturn ".$collection[1];
            } else {
                // new file
                $content = <<<EOT
<?php
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

\$collection = new RouteCollection();
EOT;
                $content .= sprintf("\n\$collection->add('%s', new Route('%s', array(", $name, $action['route']);
                $content .= sprintf("\n    '_controller' => '%s',", $controller);
                $content .= "\n)));\n\nreturn \$collection";
            }
        }

        $flink = fopen($file, 'w');
        if ($flink) {
            $write = fwrite($flink, $content);

            if ($write) {
                fclose($flink);
            } else {
                throw new \RunTimeException(sprintf('We cannot write into file "%s", has that file the correct access level?', $file));
            }
        } else {
            throw new \RunTimeException(sprintf('Problems with generating file "%s", did you gave write access to that directory?', $file));
        }
    }

    protected function parseTemplatePath($template)
    {
        $data = $this->parseLogicalTemplateName($template);

        return $data['controller'].'/'.$data['template'];
    }

    protected function parseLogicalTemplateName($logicalname, $part = '')
    {
        $data = array();

        list($data['module'], $data['controller'], $data['template']) = explode(':', $logicalname);

        return ($part ? $data[$part] : $data);
    }
}
