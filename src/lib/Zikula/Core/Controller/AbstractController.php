<?php

namespace Zikula\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Zikula\Bundle\ModuleBundle\AbstractModule;
use Zikula\Common\I18n\TranslatorAwareInterface;
use Zikula\Common\I18n\Translator;
use Zikula\Core\AbstractBundle;

class AbstractController extends Controller implements TranslatorAwareInterface
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
        $translator->setDomain($this->name);
    }
}
