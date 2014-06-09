{* purpose of this template: close an iframe from within this iframe *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        {$jcssConfig}
        <script type="text/javascript" src="{$baseurl}javascript/ajax/proto_scriptaculous.combined.min.js"></script>
        <script type="text/javascript" src="{$baseurl}javascript/helpers/Zikula.js"></script>
        <script type="text/javascript" src="{$baseurl}javascript/livepipe/livepipe.combined.min.js"></script>
        <script type="text/javascript" src="{$baseurl}javascript/helpers/Zikula.UI.js"></script>
        <script type="text/javascript" src="{$baseurl}system/ZikulaRoutesModule/Resources/public/js/ZikulaRoutesModule_editFunctions.js"></script>
    </head>
    <body>
        <script type="text/javascript">
        /* <![CDATA[ */
            // close window from parent document
            document.observe('dom:loaded', function() {
                routesCloseWindowFromInside('{{$idPrefix}}', {{if $commandName eq 'create'}}{{$itemId}}{{else}}0{{/if}});
            });
        /* ]]> */
        </script>
    </body>
</html>
