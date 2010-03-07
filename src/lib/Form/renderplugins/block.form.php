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
 * Smarty function to wrap pnFormRender generated form controls
 * with suitable form tags
 *
 */
function smarty_block_form($params, $content, &$render)
{
  if ($content)
  {
    $encodingHtml = (array_key_exists('enctype', $params) ? " enctype=\"$params[enctype]\"" : '');
    $action = htmlspecialchars(pnGetCurrentURI());
    $classString = '';
    if (isset($params['cssClass'])) {
        $classString = "class=\"$params[cssClass]\" ";
    }

    $render->PostRender();

    $out  =  "<form id=\"pnFormForm\" {$classString}action=\"$action\" method=\"post\"{$encodingHtml}>";
    $out .= $content;
    $out .= "\n<div>\n" . $render->GetStateHTML() . "\n"; // Add <div> for XHTML validation
    $out .= $render->GetIncludesHTML() . "\n";
    $out .= $render->GetAuthKeyHTML() . "
<input type=\"hidden\" name=\"pnFormEventTarget\" id=\"pnFormEventTarget\" value=\"\" />
<input type=\"hidden\" name=\"pnFormEventArgument\" id=\"pnFormEventArgument\" value=\"\" />
<script type=\"text/javascript\">
<!--
function pnFormDoPostBack(eventTarget, eventArgument)
{
  var f = document.getElementById('pnFormForm');
  if (!f.onsubmit || f.onsubmit())
  {
    f.pnFormEventTarget.value = eventTarget;
    f.pnFormEventArgument.value = eventArgument;
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
