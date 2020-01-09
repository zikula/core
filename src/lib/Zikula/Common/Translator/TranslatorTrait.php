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

namespace Zikula\Common\Translator;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TranslatorTrait
 */
trait TranslatorTrait
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    abstract public function setTranslator(TranslatorInterface $translator): void;
}
