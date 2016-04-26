<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_Exception_Redirect class.
 *
 * @deprecated since 1.4.0 return a Symfony\Component\HttpFoundation\RedirectResponse instead
 */
class Zikula_Exception_Redirect extends Zikula_Exception
{
    /**
     * Url.
     *
     * @var string
     */
    protected $url;

    /**
     * Redirect type.
     *
     * @var integer
     */
    protected $type;

    /**
     * Constructor.
     *
     * @deprecated since 1.4.0 return a Symfony\Component\HttpFoundation\RedirectResponse instead
     *
     * @param string  $url  Url.
     * @param integer $type Default 302.
     */
    public function __construct($url, $type = 302)
    {
        $this->url = $url;
        $this->type = $type;

        $response  = new Symfony\Component\HttpFoundation\RedirectResponse(System::normalizeUrl($url), $type);
        $response->send();
        exit;
    }

    /**
     * Get Url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get redirect type.
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }
}
