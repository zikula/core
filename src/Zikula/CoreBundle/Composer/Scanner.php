<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Composer;

use const JSON_ERROR_NONE;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

class Scanner
{
    /**
     * @var array
     */
    private $jsons = [];

    /**
     * @var array
     */
    private $invalid = [];

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Scans and loads composer.json files.
     */
    public function scan(array $paths = [], int $depth = 3, Finder $finder = null): void
    {
        $finder = $finder ?? new Finder();
        $finder->files()
            ->in($paths)
            ->notPath('docs')
            ->notPath('vendor')
            ->notPath('Resources')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->depth('<' . $depth)
            ->name('composer.json')
        ;

        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            $json = $this->decode($file->getRealPath());
            if (false !== $json) {
                $this->jsons[$json['name']] = $json;
            } else {
                $this->invalid[] = $file->getRelativePath();
            }
        }
    }

    /**
     * Decodes a json string.
     *
     * @return bool|mixed
     */
    public function decode(string $jsonFilePath)
    {
        $base = str_replace('\\', '/', dirname($jsonFilePath));
        $zkRoot = dirname(__DIR__, 3) . '/';
        $base = mb_substr($base, mb_strlen($zkRoot));

        $json = json_decode(file_get_contents($jsonFilePath), true);
        if (JSON_ERROR_NONE === json_last_error()) {
            // calculate PSR-4 autoloading path for this namespace
            $class = $json['extra']['zikula']['class'];
            $ns = mb_substr($class, 0, mb_strrpos($class, '\\') + 1);
            if (false === isset($json['autoload']['psr-4'][$ns])) {
                return false;
            }
            $json['autoload']['psr-4'][$ns] = $base;
            $json['extra']['zikula']['short-name'] = mb_substr($class, mb_strrpos($class, '\\') + 1, mb_strlen($class));
            $json['extensionType'] = $this->computeExtensionType($json);

            return $json;
        }

        return false;
    }

    private function computeExtensionType(array $json): int
    {
        if (ZikulaKernel::isCoreExtension($json['extra']['zikula']['short-name'])) {
            return MetaData::EXTENSION_TYPE_THEME === $json['type'] ? MetaData::TYPE_SYSTEM_THEME : MetaData::TYPE_SYSTEM_MODULE;
        }

        return MetaData::EXTENSION_TYPE_THEME === $json['type'] ? MetaData::TYPE_THEME : MetaData::TYPE_MODULE;
    }

    public function getExtensionsMetaData(): array
    {
        $array = [];
        foreach ($this->jsons as $json) {
            $array[$json['name']] = new MetaData($json);
            $array[$json['name']]->setTranslator($this->translator);
        }

        return $array;
    }

    public function getInvalid(): array
    {
        return $this->invalid;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
