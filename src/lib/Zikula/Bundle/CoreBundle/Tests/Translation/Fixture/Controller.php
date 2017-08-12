<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
    private $translator;
    private $session;

    public function __construct(TranslatorInterface $translator, SessionInterface $session)
    {
        $this->translator = $translator;
        $this->session = $session;
    }

    public function indexAction()
    {
        $this->session->setFlash('foo', $this->translator->__(/** @Desc("Foo bar") */ 'text.foo_bar'));
    }

    public function welcomeAction()
    {
        $this->session->setFlash('bar',
            /** @Desc("Welcome %name%! Thanks for signing up.") */
            $this->translator->__f('text.sign_up_successful %name%', ['%name%' => 'Johannes']));
    }

    public function foobarAction()
    {
        $this->session->setFlash('archive',
            /** @Desc("Archive Message") @Meaning("The verb (to archive), describes an action") */
            $this->translator->__('button.archive'));
    }

    public function nonExtractableButIgnoredAction()
    {
        /** @Ignore */ $this->translator->__($foo);
        /** Foobar */
        /** @Ignore */ $this->translator->__f('foo', [], $baz);
    }

    public function irrelevantDocComment()
    {
        /** @Foo @Bar */ $this->translator->__f('text.irrelevant_doc_comment', [], 'baz');
    }

    public function arrayAccess()
    {
        $arr['foo']->__('text.array_method_call');
    }

    public function assignToVar()
    {
        /** @Desc("The var %foo% should be assigned.") */
        return $this->translator->__f('text.var.assign %foo%', ['%foo%' => 'fooVar']);
    }
}
