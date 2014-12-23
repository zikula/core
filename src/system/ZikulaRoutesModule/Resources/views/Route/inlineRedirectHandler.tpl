{* purpose of this template: close an iframe from within this iframe *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        {$jcssConfig}
        <link rel="stylesheet" href="web/bootstrap/css/bootstrap.min.css" type="text/css" />
        <link rel="stylesheet" href="web/bootstrap/css/bootstrap-theme.css" type="text/css" />
        <script type="text/javascript" src="web/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="web/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="{$baseurl}javascript/helpers/Zikula.js"></script>
        <script type="text/javascript" src="{$baseurl}system/ZikulaRoutesModule/Resources/public/js/ZikulaRoutesModule.EditFunctions.js"></script>
    </head>
    <body>
        <script type="text/javascript">
        /* <![CDATA[ */
            // close window from parent document
            ( function($) {
                $(document).ready(function() {
                    zikulaRoutesCloseWindowFromInside('{{$idPrefix}}', {{if $commandName eq 'create'}}{{$itemId}}{{else}}0{{/if}});
                });
            })(jQuery);
        /* ]]> */
        </script>
    </body>
</html>
