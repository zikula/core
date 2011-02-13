<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to wrap Form_View generated form controls with suitable form tags.
 *
 * @param array     $params  Parameters passed in the block tag.
 * @param string    $content Content of the block.
 * @param Form_View $view    Reference to Form_View object.
 *
 * @return string The rendered output.
 */
function smarty_block_form($params, $content, $view)
{
    if ($content) {
        PageUtil::addVar('stylesheet', 'system/Theme/style/form/style.css');
        $encodingHtml = (array_key_exists('enctype', $params) ? " enctype=\"$params[enctype]\"" : '');
        $action = htmlspecialchars(System::getCurrentUri());
        $classString = '';
        if (isset($params['cssClass'])) {
            $classString = "class=\"$params[cssClass]\" ";
        }

        $view->postRender();

        $formId = $view->getFormId();
        $out  =  "<form id=\"FormForm\" {$classString}action=\"$action\" method=\"post\"{$encodingHtml}>";
        $out .= $content;
        $out .= "\n<div>\n" . $view->getStateHTML() . "\n"; // Add <div> for XHTML validation
        $out .= $view->getIncludesHTML() . "\n";
        $out .= $view->getCsrfTokenHtml() . "\n";
        $out .= "<input type=\"hidden\" name=\"__formid\" id=\"__formid\" value=\"{$formId}\" />\n";
        $out .= "
<input type=\"hidden\" name=\"FormEventTarget\" id=\"FormEventTarget\" value=\"\" />
<input type=\"hidden\" name=\"FormEventArgument\" id=\"FormEventArgument\" value=\"\" />
<script type=\"text/javascript\">
<!--
function FormDoPostBack(eventTarget, eventArgument)
{
  var f = document.getElementById('FormForm');
  if (!f.onsubmit || f.onsubmit())
  {
    f.FormEventTarget.value = eventTarget;
    f.FormEventArgument.value = eventArgument;
    f.submit();
  }
}
// -->
</script>
</div>\n";
        $out .= "</form>\n";
        return $out;
    }
}
