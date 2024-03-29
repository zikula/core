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
            'library' => 'PHP libraries',
            'composer-installer' => 'Composer Installers',
            'composer-plugin' => 'Composer Plugins',
            'symfony-pack' => 'Symfony Packages',
            'symfony-bridge' => 'Symfony Bridge',
            'symfony-mailer-bridge' => 'Symfony Mailer Bridge',
            'symfony-messenger-bridge' => 'Symfony Messenger Bridge',
        ];
        $types = array_keys($typeOrder);
        usort($packages, function ($a, $b) use ($types) {
            $typeOrder = array_search($a['type'], $types) - array_search($b['type'], $types);
            if (0 !== $typeOrder) {
                return $typeOrder;
            }

            // inside same type order by name
            return strcmp($a['name'], $b['name']);
        });

        $content = "---
currentMenu: vendor-info
---
# Vendor information
";

        $currentType = '';
        $authors = [];
        foreach ($packages as $package) {
            if ($currentType !== $package['type']) {
                if ('' !== $currentType) {
                    $content .= "\n";
                }
                $content .= '## ' . $typeOrder[$package['type']] . "\n\n";
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
        $content .= "## Authors\n\n";
        $content .= "These are the main authors of all of the projects supporting Zikula:\n\n";

        $tmp = [];
        foreach ($authors as $k => $author) {
            if (in_array($author['name'], $tmp)) {
                unset($authors[$k]);
                continue;
            }
            $tmp[] = $author['name'];
        }
        usort($authors, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
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

        return 0;
    }
}
