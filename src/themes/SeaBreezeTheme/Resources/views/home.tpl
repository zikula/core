<!DOCTYPE html>
<html lang="{lang}" dir="{langdirection}">
    <head>{include file="includes/head.tpl"}</head>
    <body class="threecols">
        {include file="includes/userheader.tpl"}
        <div id="pagewidth">
            <div id="wrapper" class="clearfix">
                <div id="leftcol">
                    <div id="sidebar">
                        {blockposition name=left}
                     </div>
                </div>
                <div id="maincol">
                    {blockposition name=center}
                    {$maincontent}
                </div>
                <div id="rightcol">{blockposition name=right}</div>
            </div>
        </div>
        {include file="includes/footer.tpl"}
    </body>
</html>
