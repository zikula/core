<?php
require_once __DIR__.'/app/bootstrap.php';
require_once __DIR__.'/twigify_lib.php';

if (isset($argc)) {
    if ($argc != 3) {
        die("
Usage: php twigify.php <filename> <output_filename>

 Run from the browser puts it into test mode.
");
    }

    $fileName = $argv[1];
    if (!file_exists($fileName) || !is_file($fileName)) {
        die("
        $fileName is not readable or non-existing
        ");
    }
    $outFileName = $argv[2];

    $string = file_get_contents($fileName);
} else {
    $string = file_get_contents(__DIR__.'/web/system/SearchModule/Resources/views/User/form.tpl');
}

$string = str_replace('{else}', '{% else %}', $string);
$string = str_replace('{/if}', '{% endif %}', $string);
$string = str_replace('{/foreach}', '{% endfor %}', $string);
$string = str_replace('{/foreachelse}', '{% else %}', $string);

$res = preg_match_all('/\{\s{0,}(.+?)\s{0,}\}/', $string, $matches);
$twig = new Twiggifier($string, $matches);
$twig->stripString('|safetext');
$twig->replaceString('=$', '=');
$twig->replaceString('|@json_encode', '|json_encode()');
$twig->replaceString('|escape', '|e');

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
    file_put_contents($outFileName, $twig->getTemplate());
    echo "$outFileName written.\n";
} else {
    echo "<pre>";
    echo htmlentities($twig->getTemplate());
}
