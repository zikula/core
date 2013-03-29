<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Zikula\Bundle\GeneratorBundle\Generator\ControllerGenerator;
use Zikula\Bundle\GeneratorBundle\Command\Helper\DialogHelper;

/**
 * Generates controllers.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class GenerateControllerCommand extends ContainerAwareCommand
{
    private $generator;

    /**
     * @see Command
     */
    public function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption(
                    'controller',
                    '',
                    InputOption::VALUE_REQUIRED,
                    'The name of the controller to create'
                ),
                new InputOption(
                    'route-format',
                    '',
                    InputOption::VALUE_REQUIRED,
                    'The format that is used for the routing (yml, xml, php, annotation)',
                    'annotation'
                ),
                new InputOption(
                    'template-format',
                    '',
                    InputOption::VALUE_REQUIRED,
                    'The format that is used for templating (twig, php)',
                    'twig'
                ),
                new InputOption(
                    'actions',
                    '',
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'The actions in the controller'
                ),
            ))
            ->setDescription('Generates a controller')
            ->setHelp(<<<EOT
The <info>generate:controller</info> command helps you generates new controllers
inside modules.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--module</comment> and <comment>--controller</comment> are the only
ones needed if you follow the conventions):

<info>php app/console generate:controller --controller=AcmeBlogModule:Post</info>

If you want to disable any user interaction, use <comment>--no-interaction</comment>
but don't forget to pass all needed options:

<info>php app/console generate:controller --controller=AcmeBlogModule:Post --no-interaction</info>
EOT
            )
            ->setName('generate:controller')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        if (null === $input->getOption('controller')) {
            throw new \RuntimeException('The controller option must be provided.');
        }

        list($module, $controller) = $this->parseShortcutNotation($input->getOption('controller'));
        if (is_string($module)) {
            $module = Validators::validateModuleName($module);

            try {
                $module = $this->getContainer()->get('kernel')->getBundle($module);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Module "%s" does not exists.</>', $module));
            }
        }

        $dialog->writeSection($output, 'Controller generation');

        $generator = $this->getGenerator();
        $generator->generate($module, $controller, $input->getOption('route-format'), $input->getOption('template-format'), $this->parseActions($input->getOption('actions')));

        $output->writeln('Generating the module code: <info>OK</info>');

        $dialog->writeGeneratorSummary($output, array());
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Zikula controller generator');

        // namespace
        $output->writeln(array(
            '',
            'Every page, and even sections of a page, are rendered by a <comment>controller</comment>.',
            'This command helps you generate them easily.',
            '',
            'First, you need to give the controller name you want to generate.',
            'You must use the shortcut notation like <comment>AcmeBlogModule:Post</comment>',
            '',
        ));

        while (true) {
            $controller = $dialog->askAndValidate($output, $dialog->getQuestion('Controller name', $input->getOption('controller')), array('Zikula\Bundle\GeneratorBundle\Command\Validators', 'validateControllerName'), false, $input->getOption('controller'));
            list($module, $controller) = $this->parseShortcutNotation($controller);

            try {
                $b = $this->getContainer()->get('kernel')->getBundle($module);

                if (!file_exists($b->getPath().'/Controller/'.$controller.'Controller.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Controller "%s:%s" already exists.</>', $module, $controller));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Module "%s" does not exists.</>', $module));
            }
        }
        $input->setOption('controller', $module.':'.$controller);

        // routing format
        $defaultFormat = (null !== $input->getOption('route-format') ? $input->getOption('route-format') : 'annotation');
        $output->writeln(array(
            '',
            'Determine the format to use for the routing.',
            '',
        ));
        $routeFormat = $dialog->askAndValidate($output, $dialog->getQuestion('Routing format (php, xml, yml, annotation)', $defaultFormat), array('Zikula\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'), false, $defaultFormat);
        $input->setOption('route-format', $routeFormat);

        // templating format
        $validateTemplateFormat = function($format) {
            if (!in_array($format, array('twig', 'php'))) {
                throw new \InvalidArgumentException(sprintf('The template format must be twig or php, "%s" given', $format));
            }

            return $format;
        };

        $defaultFormat = (null !== $input->getOption('template-format') ? $input->getOption('template-format') : 'twig');
        $output->writeln(array(
            '',
            'Determine the format to use for templating.',
            '',
        ));
        $templateFormat = $dialog->askAndValidate($output, $dialog->getQuestion('Template format (twig, php)', $defaultFormat), $validateTemplateFormat, false, $defaultFormat);
        $input->setOption('template-format', $templateFormat);

        // actions
        $input->setOption('actions', $this->addActions($input, $output, $dialog));

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg-white', true),
            '',
            sprintf('You are going to generate a "<info>%s:%s</info>" controller', $module, $controller),
            sprintf('using the "<info>%s</info>" format for the routing and the "<info>%s</info>" format', $routeFormat, $templateFormat),
            'for templating',
        ));
    }

    public function addActions(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $output->writeln(array(
            '',
            'Instead of starting with a blank controller, you can add some actions now. An action',
            'is a PHP function or method that executes, for example, when a given route is matched.',
            'Actions should be suffixed by <comment>Action</comment>.',
            '',
        ));

        $templateNameValidator = function($name) {
            if ('default' == $name) {
                return $name;
            }

            if (2 != substr_count($name, ':')) {
                throw new \InvalidArgumentException(sprintf('Template name "%s" does not have 2 colons', $name));
            }

            return $name;
        };

        $actions = $this->parseActions($input->getOption('actions'));

        while (true) {
            // name
            $output->writeln('');
            $actionName = $dialog->askAndValidate($output, $dialog->getQuestion('New action name (press <return> to stop adding actions)', null), function ($name) use ($actions) {
                if (null == $name) {
                    return $name;
                }

                if (isset($actions[$name])) {
                    throw new \InvalidArgumentException(sprintf('Action "%s" is already defined', $name));
                }

                if ('Action' != substr($name, -6)) {
                    throw new \InvalidArgumentException(sprintf('Name "%s" is not suffixed by Action', $name));
                }

                return $name;
            });
            if (!$actionName) {
                break;
            }

            // route
            $route = $dialog->ask($output, $dialog->getQuestion('Action route', '/'.substr($actionName, 0, -6)), '/'.substr($actionName, 0, -6));
            $placeholders = $this->getPlaceholdersFromRoute($route);

            // template
            $defaultTemplate = $input->getOption('controller').':'.substr($actionName, 0, -6).'.html.'.$input->getOption('template-format');
            $template = $dialog->askAndValidate($output, $dialog->getQuestion('Templatename (optional)', $defaultTemplate), $templateNameValidator, false, 'default');

            // adding action
            $actions[$actionName] = array(
                'name'         => $actionName,
                'route'        => $route,
                'placeholders' => $placeholders,
                'template'     => $template,
            );
        }

        return $actions;
    }

    public function parseActions($actions)
    {
        if (is_array($actions)) {
            return $actions;
        }

        $newActions = array();

        foreach (explode(' ', $actions) as $action) {
            $data = explode(':', $action);

            // name
            if (!isset($data[0])) {
                throw new \InvalidArgumentException('An action must have a name');
            }
            $name = array_shift($data);

            // route
            $route = (isset($data[0]) && '' != $data[0]) ? array_shift($data) : '/'.substr($name, 0, -6);
            if ($route) {
                $placeholders = $this->getPlaceholdersFromRoute($route);
            } else {
                $placeholders = array();
            }

            // template
            $template = (0 < count($data) && '' != $data[0]) ? implode(':', $data) : 'default';

            $newActions[$name] = array(
                'name'         => $name,
                'route'        => $route,
                'placeholders' => $placeholders,
                'template'     => $template,
            );
        }

        return $newActions;
    }

    public function getPlaceholdersFromRoute($route)
    {
        preg_match_all('/{(.*?)}/', $route, $placeholders);
        $placeholders = $placeholders[1];

        return $placeholders;
    }

    public function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The controller name must contain a : ("%s" given, expecting something like AcmeBlogModule:Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }

    protected function getGenerator()
    {
        if (null === $this->generator) {
            $this->generator = new ControllerGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton/controller');
        }

        return $this->generator;
    }

    public function setGenerator(ControllerGenerator $generator)
    {
        $this->generator = $generator;
    }

    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Zikula\Bundle\GeneratorBundle\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }

        return $dialog;
    }
}
