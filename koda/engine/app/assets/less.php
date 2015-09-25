<?php

namespace App\Assets;

use
	\App\File;

define ('LINK_CSS', '/public/css');
define ('DIR_CSS', DOCUMENT_ROOT . LINK_CSS);

/*
	Less class - compiles less files with check of age
*/

class Less {


	public static function compile($source, $dest)
	{
		$sourceFile = new File(DIR_CSS . '/' . $source);
		$destFile = new File(DIR_CSS . '/' . $dest);

		if (self :: checkIfNeedCompile($sourceFile, $destFile)) {
			require_once "lessc.inc.php";
			$less = new \lessc;
			$less -> compileFile($sourceFile -> path, $destFile -> path);
		}
	}


	public static function checkIfNeedCompile($source, $dest)
	{
		$needCompile = !$dest -> readable;
		$imports = File :: scanFiles($source -> dir, '\.less$', true);

		if (!$needCompile and $imports)
			foreach ($imports as $file) {
				if ($file -> modified > $dest -> modified) {
					$needCompile = true;
					break;
				}
			}
		return $needCompile;
	}


}
