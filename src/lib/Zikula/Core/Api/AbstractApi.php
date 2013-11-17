<?php

namespace Zikula\Core\Api;

use Zikula\Common\I18n\TranslatorAwareInterface;
use Zikula\Common\I18n\Translator;
use Zikula\Core\AbstractBundle;

abstract class AbstractApi implements TranslatorAwareInterface
{
    protected $name;

    /**
     * @var Translator
     */
    protected $trans;

    public function __construct(AbstractBundle $bundle, Translator $translator = null)
    {
        $this->name = $bundle->getName();
        $this->trans = (null === $translator) ?
            new Translator($bundle->getTranslationDomain()) : $translator;
    }

    public function setTranslator(Translator $translator)
    {
        $this->trans = $translator;
        $translator->setDomain(strtolower($this->name));
    }
}