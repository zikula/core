<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Loader;

use Symfony\Component\Config\Resource\FileResource;

/**
 * @copyright Copyright (c) 2010, Union of RAD http://union-of-rad.org (http://lithify.me/)
 */
class MoFileLoader extends ArrayLoader implements LoaderInterface
{
    /**
     * Magic used for validating the format of a MO file as well as
     * detecting if the machine used to create that file was little endian.
     *
     * @var float
     */
    const MO_LITTLE_ENDIAN_MAGIC = 0x950412de;

    /**
     * Magic used for validating the format of a MO file as well as
     * detecting if the machine used to create that file was big endian.
     *
     * @var float
     */
    const MO_BIG_ENDIAN_MAGIC = 0xde120495;

    /**
     * The size of the header of a MO file in bytes.
     *
     * @var integer Number of bytes.
     */
    const MO_HEADER_SIZE = 28;

    public function load($resource, $locale, $domain = 'messages')
    {
        $messages = $this->parse($resource);

        // empty file
        if (null === $messages) {
            $messages = array();
        }

        // not an array
        if (!is_array($messages)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a valid mo file.', $resource));
        }

        $catalogue = parent::load($messages, $locale, $domain);
        $catalogue->addResource(new FileResource($resource));

        return $catalogue;
    }

    /**
     * Parses machine object (MO) format, independent of the machine's endian it
     * was created on. Both 32bit and 64bit systems are supported.
     *
     * @param resource $stream
     * @return array
     * @throws InvalidArgumentException If stream content has an invalid format.
     */
    private function parse($resource)
    {
        $stream = fopen($resource, 'r');

        $stat = fstat($stream);

        if ($stat['size'] < self::MO_HEADER_SIZE) {
            throw new \InvalidArgumentException("MO stream content has an invalid format.");
        }
        $magic = unpack('V1', fread($stream, 4));
        $magic = hexdec(substr(dechex(current($magic)), -8));

        if ($magic == self::MO_LITTLE_ENDIAN_MAGIC) {
            $isBigEndian = false;
        } elseif ($magic == self::MO_BIG_ENDIAN_MAGIC) {
            $isBigEndian = true;
        } else {
            throw new \InvalidArgumentException("MO stream content has an invalid format.");
        }

        $header = array(
            'formatRevision' => null,
            'count' => null,
            'offsetId' => null,
            'offsetTranslated' => null,
            'sizeHashes' => null,
            'offsetHashes' => null,
        );
        foreach ($header as &$value) {
            $value = $this->readLong($stream, $isBigEndian);
        }
        extract($header);
        $messages = array();

        for ($i = 0; $i < $count; $i++) {
            $singularId = $pluralId = null;
            $translated = null;

            fseek($stream, $offsetId + $i * 8);

            $length = $this->readLong($stream, $isBigEndian);
            $offset = $this->readLong($stream, $isBigEndian);

            if ($length < 1) {
                continue;
            }

            fseek($stream, $offset);
            $singularId = fread($stream, $length);

            if (strpos($singularId, "\000") !== false) {
                list($singularId, $pluralId) = explode("\000", $singularId);
            }

            fseek($stream, $offsetTranslated + $i * 8);
            $length = $this->readLong($stream, $isBigEndian);
            $offset = $this->readLong($stream, $isBigEndian);

            fseek($stream, $offset);
            $translated = fread($stream, $length);

            if (strpos($translated, "\000") !== false) {
                $translated = explode("\000", $translated);
            }

            $ids = array('singular' => $singularId, 'plural' => $pluralId);
            $item = compact('ids', 'translated');

            if (is_array($item['translated'])) {
                $messages[$item['ids']['singular']] = stripslashes($item['translated'][0]);
                if (isset($item['ids']['plural'])) {
                    $messages[$item['ids']['plural']] = stripslashes(end($item['translated']));
                }
            } elseif($item['ids']['singular']) {
                $messages[$item['ids']['singular']] = stripslashes($item['translated']);
            }
        }

        fclose($stream);

        return array_filter($messages);
    }

    /**
     * Reads an unsigned long from stream respecting endianess.
     *
     * @param resource $stream
     * @param boolean $isBigEndian
     * @return integer
     */
    private function readLong($stream, $isBigEndian)
    {
        $result = unpack($isBigEndian ? 'N1' : 'V1', fread($stream, 4));
        $result = current($result);

        return (integer) substr($result, -8);
    }
}