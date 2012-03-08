<?php

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\Util\FormUtil;

/**
 * Symfony2 FormView Renderer.
 * 
 * Code of class Symfony\Bridge\Twig\Extension\FormExtension converted to zikula.
 */
class SystemPlugin_Symfony2Forms_FormRenderer
{
    protected $renderer;
    protected $varStack;
    protected $eventManager;

    public function __construct(Zikula_EventManagerInterface $eventManager)
    {
        $this->varStack = array();
        $this->renderer = null;
        $this->eventManager = $eventManager;
    }

    public function renderEnctype($params)
    {
        return $this->render($params['form'], 'enctype');
    }

    public function renderRow($params)
    {
        return $this->render($params['form'], 'row', $params['variables']? $params['variables'] : array());
    }

    public function renderRest($params)
    {
        $view = $params['form'];
        $variables = $params['variables'] ? $params['variables'] : array();
        
        return $this->render($view, 'rest', $variables);
    }

    public function renderWidget($params)
    {
        $view = $params['form'];
        $variables = $params['variables'] ? $params['variables'] : array();
        
        return $this->render($view, 'widget', $variables);
    }

    public function renderErrors($params)
    {
        return $this->render($params['form'], 'errors');
    }
    
    public function renderLabel($params)
    {
        $view = $params['form'];
        $label = $params['label'] ? $params['label'] : null;
        $variables = $params['variables'] ? $params['variables'] : array();
        
        if ($label !== null) {
            $variables += array('label' => $label);
        }

        return $this->render($view, 'label', $variables);
    }
    
    public function isChoiceGroup($label)
    {
        return FormUtil::isChoiceGroup($label);
    }

    public function isChoiceSelected(FormView $view, $choice)
    {
        return FormUtil::isChoiceSelected($choice, $view->get('value'));
    }

    protected function render(FormView $view, $section, array $variables = array())
    {
        $mainTemplate = in_array($section, array('widget', 'row'));
        if ($mainTemplate && $view->isRendered()) {
                return '';
        }

        $id = '_'.$view->get('proto_id', $view->get('id'));
        $template = $id . $section;
        
        $renderer = $this->getRenderer();

        if (isset($this->varStack[$template])) {
            $typeIndex = $this->varStack[$template]['typeIndex'] - 1;
            $types = $this->varStack[$template]['types'];
            $this->varStack[$template]['variables'] = array_replace_recursive($this->varStack[$template]['variables'], $variables);
        } else {
            $types = $view->get('types');
            $types[] = $id;
            $typeIndex = count($types) - 1;
            $this->varStack[$template] = array (
                'variables' => array_replace_recursive($view->all(), $variables),
                'types'     => $types,
            );
        }

        do {
            $types[$typeIndex] .= '_' . $section;

            if (isset($renderer[$types[$typeIndex]])) {

                $this->varStack[$template]['typeIndex'] = $typeIndex;

                $html = $renderer[$types[$typeIndex]]->render($view, $this->varStack[$template]['variables'], $this);

                if ($mainTemplate) {
                    $view->setRendered();
                }

                unset($this->varStack[$template]);

                return $html;
            }
        } while (--$typeIndex >= 0);

        throw new FormException(sprintf(
            'Unable to render the form as none of the following renderer exist: "%s".',
            implode('", "', array_reverse($types))
        ));
    }


    protected function getRenderer()
    {
        if($this->renderer == null) {
            $event = new Zikula_Event('symfony.formrenderer.lookup', new ArrayObject(array()));
            $this->eventManager->notify($event);
            
            $renderer = array();
            
            foreach($event->getSubject() as $render) {
                if(!$render instanceof SystemPlugin_Symfony2Forms_RendererInterface) {
                    throw new \UnexpectedValueException(get_class($render) . ' does not implement Symfony2Forms_RendererInterface');
                }
                
                $renderer[$render->getName()] = $render;
            }
            
            $this->renderer = $renderer;
        }

        return $this->renderer;
    }
    
    /**
     * Returns a renderer by name
     * @param staring $name name of a renderer, e.g. field_label
     * @return SystemPlugin_Symfony2Forms_RendererInterface 
     */
    public function getRender($name) {
        $renderer = $this->getRenderer();
        
        if(isset($renderer[$name])) {
            return $renderer[$name];
        } else {
            throw new FormException('Unknown renderer: ' . $name);
        }
    }
}
