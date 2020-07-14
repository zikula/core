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

namespace Zikula\Bundle\FormExtensionBundle\Form\Type;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\String\UnicodeString;
use Translation\Extractor\Annotation\Ignore;
use Zikula\ThemeModule\Engine\Annotation\Theme as ThemeAnnotation;

/**
 * Controller form type.
 */
class ControllerType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * The doctrine annotation reader service.
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var array
     */
    private $controllerChoices = [];

    public function __construct(
        RouterInterface $router,
        Reader $annotationReader
    ) {
        $this->router = $router;
        $this->annotationReader = $annotationReader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $supportPostRequests = in_array('request', $options['parameterTypes'], true);

        $builder->add('controller', ChoiceType::class, [
            'label' => 'Controller',
            'choices' => /** @Ignore */$this->getControllerChoices($supportPostRequests),
            'required' => $options['required'] ?? false,
            'attr' => [
                'style' => 'font-family: monospace; font-size: 10px'
            ]
        ]);
        if (in_array('query', $options['parameterTypes'], true)) {
            $builder->add('query', TextType::class, [
                'label' => 'GET parameters',
                'help' => 'Separate with &, for example: <code>foo=2&bar=5</code>',
                'help_html' => true,
                'required' => false
            ]);
        }
        if (true === $supportPostRequests) {
            $builder->add('request', TextType::class, [
                'label' => 'POST parameters',
                'help' => 'Separate with &, for example: <code>foo=2&bar=5</code>',
                'help_html' => true,
                'required' => false
            ]);
        }
        if (in_array('attributes', $options['parameterTypes'], true)) {
            $builder->add('attributes', TextType::class, [
                'label' => 'Request attributes',
                'help' => 'Separate with &, for example: <code>foo=2&bar=5</code>',
                'help_html' => true,
                'required' => false
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'zikula_controller';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'parameterTypes' => ['query', 'request', 'attributes']
        ]);
    }

    private function getControllerChoices(bool $supportPostRequests)
    {
        if (0 < count($this->controllerChoices)) {
            return $this->controllerChoices;
        }

        foreach ($this->router->getRouteCollection()->all() as $route => $params) {
            $defaults = $params->getDefaults();
            if (!isset($defaults['_controller']) || empty($defaults['_controller'])) {
                // skip routes without controller
                continue;
            }
            $controller = $defaults['_controller'] ?? '';

            // skip unwanted routes and bundles
            if ($this->isUnwantedRoute($route, $controller)) {
                continue;
            }

            // skip controllers expecting POST method only
            if (true !== $supportPostRequests) {
                $methods = $params->getMethods();
                if (1 === count($methods) && 'POST' === $methods[0]) {
                    continue;
                }
            }

            // skip controllers annotated with admin theme
            if ($this->hasAdminAnnotation($controller)) {
                continue;
            }

            $optionLabel = str_pad($route . ' ', 80, '-') . '> ' . $controller;
            $optionValue = $route . '###' . $controller;
            $this->controllerChoices[$optionLabel] = $optionValue;
        }
        ksort($this->controllerChoices);

        return $this->controllerChoices;
    }

    private function isUnwantedRoute(string $routeName, string $controllerName)
    {
        $route = new UnicodeString($routeName);
        $controller = new UnicodeString($controllerName);

        return
            $route->startsWith('_')
            || $route->startsWith('ajax')
            || $route->startsWith('bazinga')
            || null !== $route->indexOf('fos_js')
            || null !== $route->indexOf('liip_imagine')
            || null !== $route->indexOf('oro_twig_inspector')
            || null !== $route->indexOf('php_translation_profiler')
            || null !== $route->indexOf('zikula_hook_hook')
            || null !== $controller->indexOf('CoreBundle')
            || null !== $controller->indexOf('CoreInstallerBundle')
        ;
    }

    private function hasAdminAnnotation(string $controllerName)
    {
        if (false === mb_strpos($controllerName, '::')) {
            return false;
        }

        [$controllerClassName, $method] = explode('::', $controllerName);
        if (!class_exists($controllerClassName)) {
            return false;
        }

        $reflectionClass = new ReflectionClass($controllerClassName);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $themeAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, ThemeAnnotation::class);

        return isset($themeAnnotation) && 'admin' === $themeAnnotation->value;
    }
}
