<?php
if ($_SERVER['argc'] < 1) {
    die("Usage: php -f xcompile.php /full/path/to/templatefile.htm\n");
}
$filename = realpath($_SERVER['argv'][1]);
if (!file_exists($filename)) {
    die("file $filename does not exist");
}
echo "converting template: $filename\n";
$contents = file_get_contents($filename);
$original = $contents;
$contents = migrate_templates($contents);
if ($original === $contents) {
    echo "...No changes made to $filename \n";
    exit;
}
file_put_contents($filename, $contents);
echo "...Changes written to $filename \n";

function migrate_templates($content)
{
     $content = preg_replace_callback('`(<(script|style)[^>]*>)(.*?)(</\2>)`s', 'z_prefilter_add_literal_callback', $content);
     $content = str_replace('<!--[', '{', $content);
     $content = str_replace(']-->', '}', $content);
     $content = str_replace('{pn', '{', $content);
     $content = str_replace('{/pn', '{/', $content);
     $content = str_replace('|pn', '|', $content);
     $content = str_replace('|date_format', '|dateformat', $content);
     $content = str_replace('|varprepfordisplay', '|safetext', $content);
     $content = str_replace('|varprephtmldisplay', '|safehtml', $content);
     return $content;
}

function z_prefilter_add_literal_callback($matches)
{
    $tagOpen = $matches[1];
    $script = $matches[3];
    $tagClose = $matches[4];

    $script = str_replace('<!--[', '{{', str_replace(']-->', '}}', $script));

    return $tagOpen . $script . $tagClose;
}
