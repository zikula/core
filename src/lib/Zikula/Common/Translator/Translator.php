<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license    GNU/LGPLv3 (or at your option, any later version).
 * @package    Zikula
 * @subpackage Zikula_Translate
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
*/
	
namespace Zikula\Common\Translator;

use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Translator.
 *
 */
class Translator extends BaseTranslator implements WarmableInterface
{
    protected $container;
    protected $domain;
    protected $loaderIds;
    protected $options = array(
        'cache_dir' => null,
        'debug' => false,
        'resource_files' => array(),
    );
    
    /**
     * @var array
     */
    private $resourceLocales;
    /**
     * Constructor.
     *
     * Available options:
     *
     *   * cache_dir: The cache directory (or null to disable caching)
     *   * debug:     Whether to enable debugging or not (false by default)
     *   * resource_files: List of translation resources available grouped by locale.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     * @param MessageSelector    $selector  The message selector for pluralization
     * @param array              $loaderIds An array of loader Ids
     * @param array              $options   An array of options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(ContainerInterface $container, MessageSelector $selector, $loaderIds = array(), array $options = array())
    {
        $this->container = $container;
        $this->loaderIds = $loaderIds;
        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The Translator does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }
        
        $this->domain = 'zikula';
        
        $this->options = array_merge($this->options, $options);
        $this->resourceLocales = array_keys($this->options['resource_files']);
        if (null !== $this->options['cache_dir'] && $this->options['debug']) {
            $this->loadResources();
        }
        parent::__construct($container->getParameter('kernel.default_locale'), $selector, $this->options['cache_dir'], $this->options['debug']);
    }
    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        // skip warmUp when translator doesn't use cache
        if (null === $this->options['cache_dir']) {
            return;
        }
        foreach ($this->resourceLocales as $locale) {
            $this->loadCatalogue($locale);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function initializeCatalogue($locale)
    {
        $this->initialize();
        parent::initializeCatalogue($locale);
    }
    protected function initialize()
    {
        $this->loadResources();
        foreach ($this->loaderIds as $id => $aliases) {
            foreach ($aliases as $alias) {
                $this->addLoader($alias, $this->container->get($id));
            }
        }
    }   
	/*
	 * 
	 * @todo better load resource
	*/    
    private function loadResources()
    {
        foreach ($this->options['resource_files'] as $locale => $files) {
            foreach ($files as $key => $file) {
            	
            	$c = substr_count($file,".");
            	
            	if ($c < 2){
            		//explode('.', basename($file), 3);
            		// filename is domain.locale.format
            		list($domain, $format) = explode('.', basename($file), 2);           		
            	}else {
            		//explode('.', basename($file), 3);
            		// filename is domain.locale.format
            		list($domain,$locale ,$format) = explode('.', basename($file), 3);            		
            	}
				
                
            	list($domain, $format) = explode('.', basename($file), 2);
                
                $this->addResource($format, $file, $locale, $domain);
                unset($this->options['resource_files'][$locale][$key]);
            }
        }
    }
    
    /**
     * Set the translation domain.
     *
     * @param string $domain Gettext domain.
     *
     * @return void
     */
    public function setDomain($domain = null)
    {
    	$this->domain = $domain;
    }
    
    /**
     * Get translation domain.
     *
     * @return string $this->domain
     */
    public function getDomain()
    {
    	return $this->domain;
    }
    
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
    	if (null === $domain) {
    		$domain = $this->domain;
    	}
    
    	return strtr($this->getCatalogue($locale)->get((string) $id, $domain), $parameters);
    }
    
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
    	if (null === $domain) {
    		$domain = $this->domain;
    	}
    
    	$id = (string) $id;
    	$catalogue = $this->getCatalogue($locale);
    	$locale = $catalogue->getLocale();
    	while (!$catalogue->defines($id, $domain)) {
    		if ($cat = $catalogue->getFallbackCatalogue()) {
    			$catalogue = $cat;
    			$locale = $catalogue->getLocale();
    		} else {
    			break;
    		}
    	}
    
    	return strtr($this->selector->choose($catalogue->get($id, $domain), (int) $number, $locale), $parameters);
    }    
     
    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     *
     * @return string
     */
    public function __($msg)
    {
    	return $this->trans($msg, array(), $this->domain, $this->locale);
    }
    
    /**
     * Plural translations for modules.
     *
     * @param string  $m1 Singular.
     * @param string  $m2 Plural.
     * @param integer $n  Count.
     *
     * @return string
     */
    public function _n($m1, $m2, $n)
    {
    	//transChoice(string $id, integer $number, array $parameters = array(), string $domain = 'messages', string $locale = null)
    	return _n($m1, $m2, $n, $this->domain);
    }
    
    /**
     * Format translations for modules.
     *
     * @param string       $msg   Message.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function __f($msg, $param)
    {
    	return $this->trans($msg, $param, $this->domain, $this->locale);
    }
    
    /**
     * Format pural translations for modules.
     *
     * @param string       $m1    Singular.
     * @param string       $m2    Plural.
     * @param integer      $n     Count.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function _fn($m1, $m2, $n, $param)
    {
    	//transChoice(string $id, integer $number, array $parameters = array(), string $domain = 'messages', string $locale = null)
    	return _fn($m1, $m2, $n, $param, $this->domain);
    }     
}