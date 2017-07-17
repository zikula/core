<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateVendorDocCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build:generate_vendor_doc')
            ->setDescription('Generates a file containing all the vendors and the installed version.')
            ->addOption('write-to', null, InputOption::VALUE_REQUIRED, 'Where to dump the generated file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Reading composer vendors.');
        $packages = json_decode(file_get_contents('composer.lock'), true);
        $packages = $packages['packages'];

        $output->writeln('Generating output');

        $typeOrder = [
            'zikula-module' => 'Zikula Modules',
            'zikula-theme' => 'Zikula Themes',
            'symfony-bundle' => 'Symfony Bundles',
            'component' => 'Web Components',
            'library' => 'Other PHP libraries',
            'composer-installer' => 'Composer Installers',
            'composer-plugin' => 'Composer Plugins'
        ];
        $types = array_keys($typeOrder);
        usort($packages, function($a, $b) use ($types) {
            return array_search($a['type'], $types) - array_search($b['type'], $types);
        });

        $content = '';
        $currentType = '';
        $authors = [];
        foreach ($packages as $package) {
            if ($currentType != $package['type']) {
                if ($currentType != '') {
                    $content .= "\n";
                }
                $content .= $typeOrder[$package['type']] . "\n";
                $content .= str_repeat('-', strlen($typeOrder[$package['type']])) . "\n";
                $currentType = $package['type'];
            }
            $content .= "- **" . $package['name'] . "** `" . $package['version'] . "`";
            if (isset($package['license'])) {
                $content .= ", License: `" . implode(', ', $package['license']) . "`\n";
            } else {
                $content .= "\n";
            }
            if (isset($package['description'])) {
                $content .= "  *" . $package['description'] . "*\n";
            }
            if (isset($package['authors'])) {
                $authors = array_merge($authors, $package['authors']);
            }
        }

        $content .= "\n\n";
        $content .= "These are the main authors of all of the projects supporting Zikula\n";
        $content .= "-------------------------------------------------------------------\n";

        $tmp = [];
        foreach ($authors as $k => $author) {
            if (in_array($author['name'], $tmp)) {
                unset($authors[$k]);
                continue;
            }
            $tmp[] = $author['name'];
        }
        foreach ($authors as $author) {
            $content .= "- **" . $author['name'] . "**";
            if (isset($author['homepage'])) {
                $content .= " " . $author['homepage'];
            }
            if (isset($author['email'])) {
                $content .= " *" . $author['email'] . "*";
            }
            $content .= "\n";
        }

        $output->writeln('Dumping vendors to ' . $input->getOption('write-to'));

        file_put_contents($input->getOption('write-to'), $content);
    }
}
