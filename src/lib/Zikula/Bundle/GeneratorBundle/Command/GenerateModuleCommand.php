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
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Zikula\Bundle\GeneratorBundle\Generator\ModuleGenerator;
use Zikula\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;
use Zikula\Bundle\GeneratorBundle\Command\Helper\DialogHelper;

/**
 * Generates modules.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateModuleCommand extends ContainerAwareCommand
{
    private $generator;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the module to create'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where to create the module'),
                new InputOption('module-name', '', InputOption::VALUE_REQUIRED, 'The optional module name'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)'),
            ))
            ->setDescription('Generates a module')
            ->setHelp(<<<EOT
The <info>generate:module</info> command helps you generates new modules.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--namespace</comment> is the only one needed if you follow the
conventions):

<info>php app/console generate:module ----namespace=Acme/BlogModule</info>

Note that you can use <comment>/</comment> instead of <comment>\\ </comment>for the namespace delimiter to avoid any
problem.

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options:

<info>php app/console generate:module --namespace=Acme/BlogModule --dir=src [--module-name=...] --no-interaction</info>

Note that the module namespace must end with "Module".
EOT
            )
            ->setName('generate:module')
        ;
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When namespace doesn't end with Module
     * @throws \RuntimeException         When module can't be executed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        foreach (array('namespace', 'dir') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $namespace = Validators::validateModuleNamespace($input->getOption('namespace'));
        if (!$module = $input->getOption('module-name')) {
            $module = strtr($namespace, array('\\' => ''));
        }
        $module = Validators::validateModuleName($module);
        $dir = Validators::validateTargetDir($input->getOption('dir'), $module, $namespace);
        if (null === $input->getOption('format')) {
            $input->setOption('format', 'annotation');
        }
        $format = Validators::validateFormat($input->getOption('format'));

        $dialog->writeSection($output, 'Module generation');

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $generator = $this->getGenerator();
        $generator->generate($namespace, $module, $dir, $format);

        $output->writeln('Generating the module code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        // routing
        //$runner($this->updateRouting($dialog, $input, $output, $module, $format));

        $dialog->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Zikula module generator');

        // namespace
        $namespace = null;
        try {
            $namespace = $input->getOption('namespace') ? Validators::validateModuleNamespace($input->getOption('namespace')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $namespace) {
            $output->writeln(array(
                '',
                'Your application code must be written in <comment>modules</comment>. This command helps',
                'you generate them easily.',
                '',
                'Each module is hosted under a namespace (like <comment>Acme/Module/BlogModule</comment>).',
                'The namespace should begin with a "vendor" name like your company name, your',
                'project name, or your client name, followed by one or more optional category',
                'sub-namespaces, and it should end with the module name itself',
                '(which must have <comment>Module</comment> as a suffix).',
                '',
                'See http://zikula.org/doc/current/cookbook/modules/best_practices.html#index-1 for more',
                'details on module naming conventions.',
                '',
                'Use <comment>/</comment> instead of <comment>\\ </comment> for the namespace delimiter to avoid any problem.',
                '',
            ));

            $namespace = $dialog->askAndValidate($output, $dialog->getQuestion('Module namespace', $input->getOption('namespace')), array('Zikula\Bundle\GeneratorBundle\Command\Validators', 'validateModuleNamespace'), false, $input->getOption('namespace'));
            $input->setOption('namespace', $namespace);
        }

        // module name
        $module = null;
        try {
            $module = $input->getOption('module-name') ? Validators::validateModuleName($input->getOption('module-name')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $module) {
            $module = strtr($namespace, array('\\Module\\' => '', '\\' => ''));

            $output->writeln(array(
                '',
                'In your code, a module is often referenced by its name. It can be the',
                'concatenation of all namespace parts but it\'s really up to you to come',
                'up with a unique name (a good practice is to start with the vendor name).',
                'Based on the namespace, we suggest <comment>'.$module.'</comment>.',
                '',
            ));
            $module = $dialog->askAndValidate($output, $dialog->getQuestion('Module name', $module), array('Zikula\Bundle\GeneratorBundle\Command\Validators', 'validateModuleName'), false, $module);
            $input->setOption('module-name', $module);
        }

        // target dir
        $dir = null;
        try {
            $dir = $input->getOption('dir') ? Validators::validateTargetDir($input->getOption('dir'), $module, $namespace) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $dir) {
            $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

            $output->writeln(array(
                '',
                'The module can be generated anywhere. The suggested default directory uses',
                'the standard conventions.',
                '',
            ));
            $dir = $dialog->askAndValidate($output, $dialog->getQuestion('Target directory', $dir), function ($dir) use ($module, $namespace) { return Validators::validateTargetDir($dir, $module, $namespace); }, false, $dir);
            $input->setOption('dir', $dir);
        }

        // format
        $format = null;
        try {
            $format = $input->getOption('format') ? Validators::validateFormat($input->getOption('format')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $format) {
            $output->writeln(array(
                '',
                'Determine the format to use for the generated configuration.',
                '',
            ));
            $format = $dialog->askAndValidate($output, $dialog->getQuestion('Configuration format (yml, xml, php, or annotation)', $input->getOption('format')), array('Zikula\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'), false, $input->getOption('format'));
            $input->setOption('format', $format);
        }

        // optional files to generate
        $output->writeln(array(
            '',
            'To help you get started faster, the command can generate some',
            'code snippets for you.',
            '',
        ));

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s\\%s</info>\" module\nin \"<info>%s</info>\" using the \"<info>%s</info>\" format.", $namespace, $module, $dir, $format),
            '',
        ));
    }

    protected function getGenerator()
    {
        if (null === $this->generator) {
            $this->generator = new ModuleGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton/module');
        }

        return $this->generator;
    }

    public function setGenerator(ModuleGenerator $generator)
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
