<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 *
 * This tool creates an overrides.yml file for a theme. This tool must placed in the root directory of the zikula installation.
 * Usage example: php overrides.yml.php Andreas08
 */

if (!isset($argv[1])) {
	die("No theme given!\n");
}

$theme = $argv[1];

if (!file_exists('themes/'.$theme)) {
	die("Theme does not exist!\n");
}


function directoryToArray($directory, $recursive) {
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					if($recursive) {
						$array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
					}
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				} else {
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}

$files = directoryToArray("./themes/".$theme."/templates/modules", true);


$output  = "## YAML Override template\n";
$output .= "## original/file.tpl: override/file.tpl\n";
$output .= "---\n";
foreach ($files as $file) {	
	$extension = pathinfo($file, PATHINFO_EXTENSION);
	if ($extension != 'tpl' && $extension != 'html') {
		continue;
	}
	$target = substr($file, 2);
	$origine = str_replace('themes/'.$theme.'/templates/modules/', '', $target);
	$array = explode('/', $origine);
	if (!isset($array[0])) {
		continue;
	}
	$modname = $array[0];
	unset($array[0]);
	$origine = implode('/', $array);
	if (is_dir('system/'.$modname)) {
		$origine = 'system/'.$modname.'/templates/'.$origine;
	} else {
		$origine = 'modules/'.$modname.'/templates/'.$origine;
	}
	$output .= $origine.': '.$target."\n";
}

// write file
echo $output;

$override = "overrides.yml";
$fh = fopen($override, 'w') or die("can't open file");
fwrite($fh, $output);
fclose($fh);

echo 'Done. The file '.$override.' was created succesfully! Please move this file now to themes/'.$theme.'/templates/';

?>

