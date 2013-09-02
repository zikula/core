#!/usr/bin/php
<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula 1.2.x to 1.3.x migration script.
 *
 * Usage: php replace.php [/path/to/core_module_or_theme_or_file]
 */
class Bootstrapify
{
    private $_input;    
     
    /**
     * Construct function
     *
     * @param array $argv Input arguments.
     *
     * @return void
     */ 
    public function __construct($argv)
    {    
        
        // validateInputArgument
        if (empty($argv[1])) {
            echo 'Usage: '.$argv[0].' [/path/to/module_or_theme_or_file]'.PHP_EOL;
            die();
        }
        
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($argv[1]), RecursiveIteratorIterator::SELF_FIRST);
        foreach($objects as $name => $object) {
            if (strpos($name, '.svn/') === false && strpos($name, '.git/') === false) {
                                
                $filename = realpath($name);
                $content = file_get_contents($filename);
                $original = $content;
                $content = str_replace('z-informationmsg', 'alert alert-info', $content);
                $content = str_replace('z-errormsg', 'alert alert-danger', $content);
                $content = str_replace('z-statusmsg', 'alert alert-success', $content);
                $content = str_replace('z-warningmsg', 'alert alert-warning', $content);
                $content = str_replace('z-menulinks', 'navbar navbar-default', $content);
                $content = str_replace('z-datatable', 'table table-bordered', $content);
                $content = str_replace('z-hide', 'hide', $content);
                $content = str_replace('z-show', 'show', $content);

                // form
                /* This code is not very sensitive
                $content = str_replace('class="z-form"', 'class="z-form" role="form"', $content);
                $content = str_replace('"z-form"', '"form-horizontal"', $content);
                $content = str_replace('z-formrow', 'form-group', $content);
                $content = str_replace('z-formnote', 'help-block', $content);
                
                $content = preg_replace_callback("/<div class=\"form-group\">(.*?)<label(.*?)<\/label>/si", array($this, 'formCallback1'), $content);
                $content = preg_replace_callback("/<\/div>\\n( *?)<\/fieldset>/si", array($this, 'formCallback2'), $content);
                $content = preg_replace_callback("/<div id=\"(([A-Za-z]|_)*?)\">\\n( *?)<div class=\"form-group\">/si", array($this, 'formCallback3'), $content);
                $content = preg_replace_callback("/<\/div>\\n( *?)<div class=\"form-group\">/si", array($this, 'formCallback4'), $content);
                $content = preg_replace_callback("/<form class=\"form(.*?)<\/form>/si", array($this, 'formCallback5'), $content);
                */
                $content = preg_replace_callback("/<div class=\"z-buttons z-formbuttons\">(.*?)<\/div>/si", array($this, 'formCallback6'), $content);

                if ($original !== $content) {
                    file_put_contents($filename, $content);
                    echo "...Changes written to $filename \n";
                }
            }
                
        }
    }
    

    /**
     * Form replace callback function
     *
     * @param array $matches Matches array.
     *
     * @return string Migrated content
     */  
    function formCallback1($matches)
    {
        return '<div class="form-group">'.$matches[1].'<label class="col-lg-3 control-label"'.$matches[2].'</label>'.$matches[1].'<div class="col-lg-9">';

    }
    
    /**
     * Form replace callback function
     *
     * @param array $matches Matches array.
     *
     * @return string Migrated content
     */  
    function formCallback2($matches)
    {
        return '</div>'."\n".$matches[1].'</div>'."\n".$matches[1].'</fieldset>';

    }
    
    /**
     * Form replace callback function
     *
     * @param array $matches Matches array.
     *
     * @return string Migrated content
     */  
    function formCallback3($matches)
    {        
        return '</div>'."\n".$matches[3].'<div id="'.$matches[1].'">'."\n".$matches[3].'<div class="form-group">';
    }
    
    /**
     * Form replace callback function
     *
     * @param array $matches Matches array.
     *
     * @return string Migrated content
     */    
    function formCallback4($matches)
    {
        return '</div>'."\n".$matches[1].'</div>'."\n".$matches[1].'<div class="form-group">';
    }

    /**
     * Form replace callback function
     *
     * @param array $matches Matches array.
     *
     * @return string Migrated content
     */ 
    function formCallback5($matches)
    {
        $content = $matches[1];
        $content = str_replace('<textarea', '<textarea class="form-control"', $content);
        $content = str_replace('type="text"', 'type="text" class="form-control"', $content);
        $content = str_replace('type="password"', 'type="text" class="form-control"', $content);
        $content = str_replace('<select', '<select class="form-control"', $content);   
        
        return '<form class="form'.$content.'</form>';
    }
    
    
    /**
     * Form replace callback function
     *
     * @param array $matches Matches array.
     *
     * @return string Migrated content
     */ 
    function formCallback6($matches)
    {
        $content = $matches[1];
        $content = str_replace('<a', '    <a class="btn btn-default"', $content);
        $content = str_replace('{button', '    {button', $content);   
        
        return '<div class="form-group">'."\n".'            <div class="col-lg-offset-3 col-lg-9">'.$content.'    </div>'."\n".'        </div>';
    }
}

new Bootstrapify($argv);