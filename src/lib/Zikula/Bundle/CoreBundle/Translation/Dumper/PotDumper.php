<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Translation\Dumper;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Util\Writer;
use JMS\TranslationBundle\Translation\Dumper\ArrayStructureDumper;

class PotDumper extends ArrayStructureDumper
{
    private $writer;

    public function __construct()
    {
        $this->writer = new Writer();
    }

    protected function dumpStructure(array $structure)
    {
        $currentDateTime = new \DateTime();
        $this->writer
            ->reset()
            ->writeln('msgid ""')
            ->writeln('msgstr ""')
            ->writeln('"Project-Id-Version: PACKAGE VERSION\n"')
            ->writeln('"POT-Creation-Date: '.$currentDateTime->format('Y-m-d H:iO') .'\n"')
            ->writeln('"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"')
            ->writeln('"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"')
            ->writeln('"Language-Team: LANGUAGE <EMAIL@ADDRESS>\n"')
            ->writeln('"MIME-Version: 1.0\n"')
            ->writeln('"Content-Type: text/plain; charset=UTF-8\n"')
            ->writeln('"Content-Transfer-Encoding: 8bit\n"')
            ->writeln('"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\n"')
            ->writeln("\n")
        ;

        $this->dumpStructureRecursively($structure);

        return $this->writer->getContent();
    }

    private function dumpStructureRecursively(array $structure)
    {
        $isFirst = true;
        foreach ($structure as $k => $v) {
            if ($isMessage = $v instanceof Message) {
                /** @var Message $v */
                $desc = $v->getDesc();
                $meaning = $v->getMeaning();

                if (!$isFirst && ($desc || $meaning)) {
                    $this->writer->write("\n");
                }

                foreach ($v->getSources() as $source) {
                    $this->writer->writeln('#: ' . $source);
                }
                if ($desc) {
                    $this->writer->writeln('# Desc: '.$desc);
                }
                if ($meaning) {
                    $this->writer->writeln('# Meaning: '.$meaning);
                }
                $this->writer->writeln('msgid "' . $v->getId() . '"')
                    ->writeln('msgstr ""');
            } elseif (!$isFirst) {
                $this->writer->write("\n");
            }
        }
    }
}
