<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation;

/**
 * IdentityTranslator does not translate anything.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class IdentityTranslator implements TranslatorInterface
{
    private $selector;

    /**
     * Constructor.
     *
     * @param MessageSelector $selector The message selector for pluralization
     *
     * @api
     */
    public function __construct(MessageSelector $selector)
    {
        $this->selector = $selector;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function setLocale($locale)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getLocale()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return strtr($id, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return strtr($this->selector->choose($id, (int) $number, $locale), $parameters);
    }
}
