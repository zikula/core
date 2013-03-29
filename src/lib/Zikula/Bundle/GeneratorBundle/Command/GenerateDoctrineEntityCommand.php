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

use Zikula\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Zikula\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\DBAL\Types\Type;

/**
 * Initializes a Doctrine entity inside a module.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateDoctrineEntityCommand extends GenerateDoctrineCommand
{
    private $generator;

    protected function configure()
    {
        $this
            ->setName('doctrine:generate:entity')
            ->setAliases(array('generate:doctrine:entity'))
            ->setDescription('Generates a new Doctrine entity inside a module')
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)')
            ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'The fields to create with the new entity')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation')
            ->addOption('with-repository', null, InputOption::VALUE_NONE, 'Whether to generate the entity repository or not')
            ->setHelp(<<<EOT
The <info>doctrine:generate:entity</info> task generates a new Doctrine
entity inside a module:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogModule:Blog/Post</info>

The above command would initialize a new entity in the following entity
namespace <info>Acme\BlogModule\Entity\Blog\Post</info>.

You can also optionally specify the fields you want to generate in the new
entity:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogModule:Blog/Post --fields="title:string(255) body:text"</info>

The command can also generate the corresponding entity repository class with the
<comment>--with-repository</comment> option:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogModule:Blog/Post --with-repository</info>

By default, the command uses annotations for the mapping information; change it
with <comment>--format</comment>:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogModule:Blog/Post --format=yml</info>

To deactivate the interaction mode, simply use the `--no-interaction` option
without forgetting to pass all needed options:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogModule:Blog/Post --format=annotation --fields="title:string(255) body:text" --with-repository --no-interaction</info>
EOT
        );
    }

    /**
     * @throws \InvalidArgumentException When the module doesn't end with Module (Example: "Module/MySampleModule")
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

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($module, $entity) = $this->parseShortcutNotation($entity);
        $format = Validators::validateFormat($input->getOption('format'));
        $fields = $this->parseFields($input->getOption('fields'));

        $dialog->writeSection($output, 'Entity generation');

        $module = $this->getContainer()->get('kernel')->getBundle($module);

        $generator = $this->getGenerator();
        $generator->generate($module, $entity, $format, array_values($fields), $input->getOption('with-repository'));

        $output->writeln('Generating the entity code: <info>OK</info>');

        $dialog->writeGeneratorSummary($output, array());
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Doctrine2 entity generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate Doctrine2 entities.',
            '',
            'First, you need to give the entity name you want to generate.',
            'You must use the shortcut notation like <comment>AcmeBlogModule:Post</comment>.',
            ''
        ));

        $moduleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        while (true) {
            $entity = $dialog->askAndValidate($output, $dialog->getQuestion('The Entity shortcut name', $input->getOption('entity')), array('Zikula\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'), false, $input->getOption('entity'), $moduleNames);

            list($module, $entity) = $this->parseShortcutNotation($entity);

            // check reserved words
            if ($this->getGenerator()->isReservedKeyword($entity)){
                $output->writeln(sprintf('<bg=red> "%s" is a reserved word</>.', $entity));
                continue;
            }

            try {
                $b = $this->getContainer()->get('kernel')->getBundle($module);

                if (!file_exists($b->getPath().'/Entity/'.str_replace('\\', '/', $entity).'.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Entity "%s:%s" already exists</>.', $module, $entity));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Module "%s" does not exist.</>', $module));
            }
        }
        $input->setOption('entity', $module.':'.$entity);

        // format
        $output->writeln(array(
            '',
            'Determine the format to use for the mapping information.',
            '',
        ));

        $formats = array('yml', 'xml', 'php', 'annotation');

        $format = $dialog->askAndValidate($output, $dialog->getQuestion('Configuration format (yml, xml, php, or annotation)', $input->getOption('format')), array('Zikula\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'), false, $input->getOption('format'), $formats);
        $input->setOption('format', $format);

        // fields
        $input->setOption('fields', $this->addFields($input, $output, $dialog));

        // repository?
        $output->writeln('');
        $withRepository = $dialog->askConfirmation($output, $dialog->getQuestion('Do you want to generate an empty repository class', $input->getOption('with-repository') ? 'yes' : 'no', '?'), $input->getOption('with-repository'));
        $input->setOption('with-repository', $withRepository);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s:%s</info>\" Doctrine2 entity", $module, $entity),
            sprintf("using the \"<info>%s</info>\" format.", $format),
            '',
        ));
    }

    private function parseFields($input)
    {
        if (is_array($input)) {
            return $input;
        }

        $fields = array();
        foreach (explode(' ', $input) as $value) {
            $elements = explode(':', $value);
            $name = $elements[0];
            if (strlen($name)) {
                $type = isset($elements[1]) ? $elements[1] : 'string';
                preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                $type = isset($matches[1][0]) ? $matches[1][0] : $type;
                $length = isset($matches[2][0]) ? $matches[2][0] : null;

                $fields[$name] = array('fieldName' => $name, 'type' => $type, 'length' => $length);
            }
        }

        return $fields;
    }

    private function addFields(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $fields = $this->parseFields($input->getOption('fields'));
        $output->writeln(array(
            '',
            'Instead of starting with a blank entity, you can add some fields now.',
            'Note that the primary key will be added automatically (named <comment>id</comment>).',
            '',
        ));
        $output->write('<info>Available types:</info> ');

        $types = array_keys(Type::getTypesMap());
        $count = 20;
        foreach ($types as $i => $type) {
            if ($count > 50) {
                $count = 0;
                $output->writeln('');
            }
            $count += strlen($type);
            $output->write(sprintf('<comment>%s</comment>', $type));
            if (count($types) != $i + 1) {
                $output->write(', ');
            } else {
                $output->write('.');
            }
        }
        $output->writeln('');

        $fieldValidator = function ($type) use ($types) {
            // FIXME: take into account user-defined field types
            if (!in_array($type, $types)) {
                throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
            }

            return $type;
        };

        $lengthValidator = function ($length) {
            if (!$length) {
                return $length;
            }

            $result = filter_var($length, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 1)
            ));

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid length "%s".', $length));
            }

            return $length;
        };

        while (true) {
            $output->writeln('');
            $self = $this;
            $columnName = $dialog->askAndValidate($output, $dialog->getQuestion('New field name (press <return> to stop adding fields)', null), function ($name) use ($fields, $self) {
                if (isset($fields[$name]) || 'id' == $name) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
                }

                // check reserved words
                if ($self->getGenerator()->isReservedKeyword($name)){
                    throw new \InvalidArgumentException(sprintf('Name "%s" is a reserved word.', $name));
                }

                return $name;
            });
            if (!$columnName) {
                break;
            }

            $defaultType = 'string';

            // try to guess the type by the column name prefix/suffix
            if (substr($columnName, -3) == '_at') {
                $defaultType = 'datetime';
            } elseif (substr($columnName, -3) == '_id') {
                $defaultType = 'integer';
            } elseif (substr($columnName, 0, 3) == 'is_') {
                $defaultType = 'boolean';
            } elseif (substr($columnName, 0, 4) == 'has_') {
                $defaultType = 'boolean';
            }

            $type = $dialog->askAndValidate($output, $dialog->getQuestion('Field type', $defaultType), $fieldValidator, false, $defaultType, $types);

            $data = array('columnName' => $columnName, 'fieldName' => lcfirst(Container::camelize($columnName)), 'type' => $type);

            if ($type == 'string') {
                $data['length'] = $dialog->askAndValidate($output, $dialog->getQuestion('Field length', 255), $lengthValidator, false, 255);
            }

            $fields[$columnName] = $data;
        }

        return $fields;
    }

    public function getGenerator()
    {
        if (null === $this->generator) {
            $this->generator = new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
        }

        return $this->generator;
    }

    public function setGenerator(DoctrineEntityGenerator $generator)
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
