<?php declare(strict_types=1);
echo $namespace . "\n";
echo str_repeat("=", mb_strlen($namespace)) . "\n";
?>

This extension was generated by CoreBundle.

In order to use other make:foo commands, you must change the root_namespace value in
`/config/packages/dev/maker.yaml` to `<?php echo $namespace; ?>`.
