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

namespace Zikula\CoreBundle\Translation;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorTrait
{
    private TranslatorInterface $translator;

    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    public function getTranslator(): TranslatorInterface
    {
        if (null === $this->translator) {
            throw new \ErrorException('Translator must be set in __TRAIT__ before it can be used.');
        }

        return $this->translator;
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
