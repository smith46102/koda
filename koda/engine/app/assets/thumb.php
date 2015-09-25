<?php

namespace App\Assets;

use
	\App\File;

/*
	Thumb class makes thumb from an image
*/

class Thumb {

	public static
		$background = 0xFFFFFF,
		$quality = 85;


	/**
	 * Create a thumbnail from big image function checks, if thumb already exists
	 * Returns path of result image file.
	 *
	 * @param string $source - source image path
	 * @param string $dest - dest image path
	 * @param int $width - cropped image width
	 * @param int $height - cropped image height
	 * @param int $maxWidth - max cropped image width if width = 0|auto
	 * @param int $maxHeight - max cropped image height if height = 0|auto
	 * @return string
	 */
	public static function make($source, $dest, $width, $height, $maxWidth = 0, $maxHeight = 0)
	{
		$sourceFile = new File(DOCUMENT_ROOT . $source);
		$destFile = new File(DOCUMENT_ROOT . $dest);
		$destDir = new File($destFile -> dir);

		if (!$sourceFile -> readable or !$destDir -> writable) {
			throw new \Exception('create thumb - access not allowed: '. $sourceFile -> path . ', ' . $destDir -> path);
			return null;
		}

		if (self :: cropImage($sourceFile, $destFile, $width, $height, $maxWidth, $maxHeight)) {
			return $destFile -> path;
		}

		return null;
	}


	/**
	 * Read image, get mimetype,
	 * render cropped image to thumb .jpg image
	 * Returns true if crop is success, and image saved
	 *
	 * @return boolean
	 */
	private static function cropImage($source, $dest, $width, $height, $maxWidth = 0, $maxHeight = 0)
	{
		if (false === $size = getimagesize($source -> path)) {
			throw new \Exception('incorrect image format, cant read size: ' . $source -> path);
			return false;
		}

		$format = str_replace('image/', '', $size['mime']);
		$icfunc = "imagecreatefrom" . $format;

		if (!function_exists($icfunc)) {
			throw new \Exception('wrong source image format to crop: ' . $source -> path);
			return false;
		}

		$crop = self :: calcCropSize($size, $width, $height, $maxWidth, $maxHeight);
		$imageSource = $icfunc($source -> path);
		$imageDest = imagecreatetruecolor($crop['width'], $crop['height']);

		imagefill($imageDest, 0, 0, self :: $background);
		imagecopyresampled($imageDest, $imageSource,
			$crop['left'], $crop['top'], 0, 0, $crop['width'], $crop['height'],
			$size[0], $size[1]);
		imagejpeg($imageDest, $dest -> path, self :: $quality);
		imagedestroy($imageSource);
		imagedestroy($imageDest);

		return true;
	}


	// calculating crop sizes
	private static function calcCropSize($size, $width, $height, $maxWidth = 0, $maxHeight = 0)
	{
		$new_left = 0;
		$new_top = 0;

		if ($width && $height) {
			$x_ratio = $width / $size[0];
			$y_ratio = $height / $size[1];
			$ratio = $x_ratio;
			$ratio = max($x_ratio, $y_ratio);
			$use_x_ratio = ($x_ratio == $ratio);
			$new_width = $use_x_ratio ? $width : floor($size[0] * $ratio);
			$new_height = !$use_x_ratio ? $height : floor($size[1] * $ratio);
			$new_left = $use_x_ratio ? 0 : floor(($width - $new_width) / 2);
			$new_top = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

		} elseif ($width && $height == 0) {
			$new_width = $width;
			$new_height = $size[1] * ($width / $size[0]);
			if ($maxHeight !== 0 && $new_height > $maxHeight) {
				$new_height = $maxHeight;
			}

		} elseif ($width == 0 && $height) {
			$new_width = $size[0] * ($height / $size[1]);
			$new_height = $height;
			if ($maxWidth !== 0 && $new_width > $maxWidth) {
				$new_width = $maxWidth;
			}

		} else {
			$new_width = $size[0];
			$new_height = $size[1];
			$width = $size[0];
			$height = $size[1];
		}

		return [
			'width' => $new_width,
			'height' => $new_height,
			'left' => $new_left,
			'top' => $new_top
		];
	}

}