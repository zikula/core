<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="<?php echo ZLanguage::getLanguageCode(); ?>" dir="<?php echo ZLanguage::getDirection(); ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo ZLanguage::getEncoding(); ?>" />
<title><?php __('The site is currently off-line.');?></title>
<base href="<?php echo pnGetBaseURL(); ?>" />
<style type="text/css">
<!--
body {
    text-align: center;
}
#login {
    visibility:hidden;
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
}
a {
    color: #000099;
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    margin: auto;
}
img {
    border: none;
}
h1, h2 {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 18px;
    padding: 0 0 20px 0;
}
h2 {
    font-size: 12px;
}
-->
</style>
<script type="text/javascript">
<!--
function toggleLoginBox()
{
    document.getElementById('login').style.visibility = (document.getElementById('login').style.visibility == 'visible') ? 'hidden' : 'visible';
}
// -->
</script>
</head>
<body>
<img src="images/icons/large/error.gif" alt="<?php __('The site is currently off-line.'); ?>" width="48" height="48" />
<h1><?php echo __('The site is currently off-line.'); ?></h1>
<h2><?php echo (System::VERSION_NUM != System::getVar('Version_Num')) ? __('This site needs to be upgraded, please contact the system administrator.') : System::getVar('siteoffreason');?></h2>
<a href="#" onclick="toggleLoginBox();" title="<?php echo __('Administrator log-in'); ?>"><?php echo __('Administrator log-in'); ?></a>
<form id="login" action="<?php System::getVar('entrypoint', 'index.php'); ?>?module=Users&amp;func=siteofflogin" method="post">
    <div>
        <p><?php echo __('An administrator log-in is required.'); ?>:</p>
        <div><label for="user"><?php echo __('User name'); ?>: </label><input id="user" type="text" name="user" size="14" maxlength="64" /></div>
        <div><label for="pass"><?php echo __('Password'); ?>: </label><input id="pass" type="password" name="pass" size="14" maxlength="20" /></div>
        <div><label for="rememberme"><?php echo __('Remember me'); ?></label><input id="rememberme" type="checkbox" value="1" name="rememberme" /></div>
        <div><input type="submit" value="<?php echo __('Log in'); ?>" /></div>
    </div>
</form>
</body>
</html>