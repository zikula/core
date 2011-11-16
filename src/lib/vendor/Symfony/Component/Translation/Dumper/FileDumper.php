<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Dumper;

use Symfony\Component\Translation\MessageCatalogue;

/**
 * FileDumper is an implementation of DumperInterface that dump a message catalogue to file(s).
 * Performs backup of already existing files.
 *
 * Options:
 * - path (mandatory): the directory where the files should be saved
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
abstract class FileDumper implements DumperInterface
{
    /**
     * {@inheritDoc}
     */
    public function dump(MessageCatalogue $messages, $options = array())
    {
        if (!array_key_exists('path', $options)) {
            throw new \InvalidArgumentException('The file dumper need a path options.');
        }

        // save a file for each domain
        foreach ($messages->getDomains() as $domain) {
            $file = $domain.'.'.$messages->getLocale().'.'.$this->getExtension();
            // backup
            if (file_exists($options['path'].$file)) {
                copy($options['path'].$file, $options['path'].'/'.$file.'~');
            }
            // save file
            file_put_contents($options['path'].'/'.$file, $this->format($messages, $domain));
        }
    }

    /**
     * Transforms a domain of a message catalogue to its string representation.
     *
     * @return The string representation
     */
    abstract protected function format(MessageCatalogue $messages, $domain);

    /**
     * Gets the file extension of the dumper.
     *
     * @return The file extension
     */
    abstract protected function getExtension();
}
