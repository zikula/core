<?php

namespace Zikula\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Zikula\Bundle\ModuleBundle\AbstractModule;
use Zikula\Common\I18n\TranslatorAwareInterface;
use Zikula\Common\I18n\Translator;
use Zikula\Core\AbstractBundle;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class AbstractController extends Controller implements TranslatorAwareInterface
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

    /**
     * Returns a rendered view.
     *
     * @param string $view       The view name
     * @param array  $parameters An array of parameters to pass to the view
     *
     * @return string The rendered view
     */
    public function renderView($view, array $parameters = array())
    {
        $parameters = $this->decorateTranslator($parameters);

        return parent::renderView($view, $parameters);
    }

    /**
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parameters = $this->decorateTranslator($parameters);

        return parent::render($view, $parameters, $response);
    }

    /**
     * Streams a view.
     *
     * @param string           $view       The view name
     * @param array            $parameters An array of parameters to pass to the view
     * @param StreamedResponse $response   A response instance
     *
     * @return StreamedResponse A StreamedResponse instance
     */
    public function stream($view, array $parameters = array(), StreamedResponse $response = null)
    {
        $parameters = $this->decorateTranslator($parameters);

        return parent::stream($view, $parameters, $response);
    }

    protected function decorateTranslator(array $parameters)
    {
        $parameters['p'] = $this->trans;
        $parameters['d'] = $this->trans->getDomain();

        return $parameters;
    }
}
