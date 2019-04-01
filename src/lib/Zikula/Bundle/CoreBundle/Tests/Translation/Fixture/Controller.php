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

namespace Zikula\Bundle\CoreBundle\Tests\Translation\Fixture;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * This is a sample controller class.
 *
 * It is used in unit tests to extract translations, and their respective description,
 * and meaning if specified.
 */
class Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(TranslatorInterface $translator, SessionInterface $session)
    {
        $this->translator = $translator;
        $this->session = $session;
    }

    public function indexAction(): void
    {
        $this->session->getFlashBag()->add('foo', $this->translator->__(/** @Desc("Foo bar") */ 'text.foo_bar'));
    }

    public function welcomeAction(): void
    {
        $this->session->getFlashBag()->add('bar',
            /** @Desc("Welcome %name%! Thanks for signing up.") */
            $this->translator->__f('text.sign_up_successful %name%', ['%name%' => 'Johannes']));
    }

    public function foobarAction(): void
    {
        $this->session->getFlashBag()->add('archive',
            /** @Desc("Archive Message") @Meaning("The verb (to archive), describes an action") */
            $this->translator->__('button.archive'));
    }

    public function nonExtractableButIgnoredAction(): void
    {
        /** @Ignore */ $this->translator->__($foo);
        /** Foobar */
        /** @Ignore */ $this->translator->__f('foo', [], $baz);
    }

    public function irrelevantDocComment(): void
    {
        /** @Foo @Bar */ $this->translator->__f('text.irrelevant_doc_comment', [], 'baz');
    }

    public function arrayAccess(): void
    {
        $arr['foo']->__('text.array_method_call');
    }

    public function assignToVar(): string
    {
        /** @Desc("The var %foo% should be assigned.") */
        return $this->translator->__f('text.var.assign %foo%', ['%foo%' => 'fooVar']);
    }
}
