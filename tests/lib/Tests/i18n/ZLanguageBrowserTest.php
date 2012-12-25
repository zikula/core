<?php

/**
 * @backupGlobals enabled
 */
class ZLanguageBrowserTest extends PHPUnit_Framework_TestCase
{
    public function testEmptySysLang()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'not_empty';
        $sysLang = '';
        $ZLanguageBrowser = new ZLanguageBrowser($sysLang);
        $false = $ZLanguageBrowser->discover();
        $this->assertFalse($false);
    }

    public function testEmptyServerAcceptLang()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
        $sysLang = array('fr');
        $ZLanguageBrowser = new ZLanguageBrowser($sysLang);
        $false = $ZLanguageBrowser->discover();
        $this->assertFalse($false);
    }

    public function testReturnBrowserPrefFR()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en;q=0.3,fr;q=0.8';
        $sysLang = array('fr', 'en', 'de', 'nl');
        $ZLanguageBrowser = new ZLanguageBrowser($sysLang);
        $return = $ZLanguageBrowser->discover();
        $this->assertEquals('fr', $return);
    }

    public function testReturnBrowserPrefFRBis()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en;q=0.3,fr';
        $sysLang = array('fr', 'en', 'de', 'nl');
        $ZLanguageBrowser = new ZLanguageBrowser($sysLang);
        $return = $ZLanguageBrowser->discover();
        $this->assertEquals('fr', $return);
    }

    public function testReturnBrowserPrefEN()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en;q=0.8,fr;q=0.3';
        $sysLang = array('fr', 'en', 'de', 'nl');
        $ZLanguageBrowser = new ZLanguageBrowser($sysLang);
        $return = $ZLanguageBrowser->discover();
        $this->assertEquals('en', $return);
    }

    public function testReturnBrowserPrefFalse()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es;q=0.8,pl;q=0.3';
        $sysLang = array('fr', 'en', 'de', 'nl');
        $ZLanguageBrowser = new ZLanguageBrowser($sysLang);
        $return = $ZLanguageBrowser->discover();
        $this->assertFalse($return);
    }
}

