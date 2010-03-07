<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * E-mail input for pnForms
 *
 * The e-mail input plugin is a text input plugin that only allows e-mails to be posted.
 *
 * You can also use all of the features from the pnFormTextInput plugin since the e-mail input
 * inherits from it.
 */
class Form_Plugin_EMailInput extends Form_Plugin_TextInput
{
    function getFilename()
    {
        return __FILE__;
    }
    
    function create(&$render, &$params)
    {
        $this->maxLength = 100;
        
        parent::create($render, $params);
        
        $this->cssClass .= ' email';
    }
    
    function validate(&$render)
    {
        parent::validate($render);
        if (!$this->isValid) {
            return;
        }
        
        if (!empty($this->text)) {
            if (!pnVarValidate($this->text, 'email')) {
                $this->setError(__('Error! Invalid e-mail address.'));
            }
        }
    }
}

