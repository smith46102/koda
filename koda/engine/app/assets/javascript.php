<?php

namespace App\Assets;

use
	\App\File;

define ('LINK_JS', '/public/js');
define ('DIR_JS', DOCUMENT_ROOT . LINK_JS);

/*
	Javascript class - compiles js files with check of age
*/

class Javascript {

	// compiles $source (JS) -> $dest (JS),
	// compares sources modify time in this folder with age of $dest
	// and compiles only if $dest is newer
	public static function compile($source, $dest)
	{
		$sourceFile = new File(DIR_JS . '/' . $source);
		$destFile = new File(DIR_JS . '/' . $dest);

		if (self :: checkIfNeedCompile($sourceFile, $destFile)) {
			$content = self :: parse($sourceFile);
			$destFile -> save($content);
		}
	}


	// check modify time of sources
	public static function checkIfNeedCompile($source, $dest)
	{
		$needCompile = !$dest -> readable;
		$imports = File :: scanFiles($source -> dir, '\.js$', true);

		if (!$needCompile and $imports)
			foreach ($imports as $file) {
				if ($file -> modified > $dest -> modified) {
					$needCompile = true;
					break;
				}
			}
		return $needCompile;
	}


	// replace @import directive in source JS files
	// with file contents
	public static function parse($source)
	{
		$contents = $source -> read();
		return preg_replace_callback('/@import "(.*?)";/', function ($matches) use ($source) {
			$importFile = new File($source -> dir . '/' . $matches[1]);
			return self :: parse($importFile);
		}, $contents);
	}

}