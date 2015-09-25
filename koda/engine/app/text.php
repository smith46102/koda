<?php

namespace App;

/*
	Text class implements utils text functions and converters
	used to:
	1) convert ru-text to valid urls and filenames
	2) convert dates in Mysql format to dates in human form
*/

class Text {

	private static $RU_EN_CONVERTER = [
		'а' => 'a', 'б' => 'b', 'в' => 'v',
		'г' => 'g', 'д' => 'd', 'е' => 'e',
		'ё' => 'e', 'ж' => 'zh',  'з' => 'z',
		'и' => 'i', 'й' => 'y', 'к' => 'k',
		'л' => 'l', 'м' => 'm', 'н' => 'n',
		'о' => 'o', 'п' => 'p', 'р' => 'r',
		'с' => 's', 'т' => 't', 'у' => 'u',
		'ф' => 'f', 'х' => 'h', 'ц' => 'c',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
		'ь' => '', 'ы' => 'y', 'ъ' => '',
		'э' => 'e', 'ю' => 'yu',  'я' => 'ya',
		'А' => 'A', 'Б' => 'B', 'В' => 'V',
		'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
		'Ё' => 'E', 'Ж' => 'Zh',  'З' => 'Z',
		'И' => 'I', 'Й' => 'Y', 'К' => 'K',
		'Л' => 'L', 'М' => 'M', 'Н' => 'N',
		'О' => 'O', 'П' => 'P', 'Р' => 'R',
		'С' => 'S', 'Т' => 'T', 'У' => 'U',
		'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
		'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
		'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
	];

	private static $MONTHS = [
		'', 'января', 'февраля', 'марта',
		'апреля', 'мая', 'июня',
		'июля', 'августа', 'сентября',
		'октября', 'ноября', 'декабря'
	];

	private static $SHORT_MONTHS = [
		'', 'янв', 'фев', 'мар',
		'апр', 'мая', 'июн',
		'июл', 'авг', 'сен',
		'окт', 'ноя', 'дек'
	];


	/**
	 * Translit Ru string to En
	 *
	 * @param string $string
	 * @return string
	 */
	private static function rus2translit($string)
	{
		return strtr($string, self :: $RU_EN_CONVERTER);
	}


	/**
	 * Translit RuEn str to site url
	 * Replace all wrong symbols with '_'
	 *
	 * @param string $str
	 * @return string
	 */
	public static function strToUrl($str)
	{
		$str = rus2translit($str);
		$str = strtolower($str);
		$str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
		return trim(substr($str, 0, 50), "-");
	}


	/**
	 * Translit str to normal filename
	 * Replace all wrong symbols to '-'
	 *
	 * @param string $str
	 * @return string
	 */
	public static function strToFilename($str)
	{
		$str = rus2translit($str);
		$str = strtolower($str);
		return preg_replace('~[^-a-z0-9_]+~u', '_', $str);
	}


	/**
	 * Makes datetime in human format
	 *
	 * @param string $date
	 * @return string
	 */
	public static function datetime($date)
	{
		if (preg_match('/^(\d{4})-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/', $date, $m)) {
			$day = intval($m[3]);
			return $day . ' '. self :: $MONTHS[intval($m[2])].' '.$m[1].', '.$m[4].':'.$m[5];
		}
		return null;
	}


	/**
	 * Makes date in human format
	 *
	 * @param string $date
	 * @return string
	 */
	public static function date($date) {
		if (preg_match('/^(\d{4})-(\d\d)-(\d\d) \d\d:\d\d:\d\d$/', $date, $m)) {
			$day = intval($m[3]);
			return $day . ' '. self :: $MONTHS[intval($m[2])].' '.$m[1];
		}
		return null;
	}


	/**
	 * Makes short date in human format
	 *
	 * @param string $date
	 * @return string
	 */
	public static function shortdate($date)
	{
		if (preg_match('/^(\d{4})-(\d\d)-(\d\d) \d\d:\d\d:\d\d$/', $date, $m)) {
			$day = intval($m[3]);
			return $day . ' '. self :: $SHORT_MONTHS[intval($m[2])];
		}
		return null;
	}


}