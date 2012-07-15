<?php
require_once __DIR__.'/app/bootstrap.php';
require_once __DIR__.'/twigify_lib.php';

if (isset($argc)) {
    if ($argc != 2) {
        die("
Usage: php twigify.php <filename>

 Run from the browser puts it into test mode.
");
    }

    $fileName = $argv[1];
    if (!file_exists($fileName) || !is_file($fileName)) {
        die("
        $fileName is not readable or non-existing
        ");
    }

    $string = file_get_contents($fileName);
} else {
    $string = file_get_contents(__DIR__.'/web/system/SettingsModule/Resources/views/Admin/modifyconfig.tpl');
}

$string = str_replace('{/else}', '{% else %}', $string);
$string = str_replace('{/if}', '{% endif %}', $string);
$string = str_replace('{/for}', '{% endfor %}', $string);

$res = preg_match_all('/\{\s{0,}(.+?)\s{0,}\}/', $string, $matches);
$twig = new Twiggifier($string, $matches);
$twig->stripString('|safetext');
$twig->replaceString('=$', '=');

$res = preg_match_all('/\{\s{0,}(.+?)\s{0,}\}/', $twig->getTemplate(), $matches);
$twig = new Twiggifier($twig->getTemplate(), $matches);
foreach ($matches[1] as $key => $match) {
    if (preg_match('/^\$/', $twig->resolve($match))) {
        $twig->convertVariable($key);
    }
}

$res = preg_match_all('/\{\s{0,}(.+?)\s{0,}\}/', $twig->getTemplate(), $matches);
$twig = new Twiggifier($twig->getTemplate(), $matches);
foreach ($matches[1] as $key => $match) {
    $command = 'convert'.ucfirst($twig->resolve($match));
    if (method_exists($twig, $command)) {
        $twig->$command($match, $key);
    }
}

if (isset($fileName)) {
    file_put_contents($fileName.'.new', $twig->getTemplate());
    echo "File written\n";
} else {
    echo "<pre>";
    echo htmlentities($twig->getTemplate());
}
