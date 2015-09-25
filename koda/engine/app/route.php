<?php

namespace App;

/*

	Route - for add routes
	and choose controller to render html page.

	basic routing:

		Route :: add('auth,map,order');
		Route :: go();

*/

class Route {

	public static

		/**
		 * @var routes map
		 */
		$map = [],

		/**
		 * @var current initialized controller
		 */
		$controller = null;


	/**
	 * Add map to routing.
	 * Map is arrya, what specifies Routes and Controllers, what will serves whem.
	 *
	 * @param array $map
	 * @return void
	 */
	public static function add(array $map)
	{
		self :: $map = self :: $map + $map;
	}


	/**
	 * Scan routes map, match route that suits for current URL
	 * and run contoller, defined by this route.
	 * If route and controller run ok, returns true, else runs 404 controller and returns false
	 *
	 * @return boolean
	 */
	public static function go()
	{
		foreach (self :: $map as $route => $controller) {
			if (strpos(Request :: $path, $route) === 0) {
				$cname = "Controllers\\$controller";
				if (self :: runController($cname)) {
					return true;
				} else {
					self :: runController("Controllers\\Error404");
					return false;
				}
			}
		}
		return false;
	}


	/**
	 * Create, init and run controller, specified by $name
	 *
	 * @param string $name
	 * @return boolean
	 */
	private static function runController($name)
	{
		try {
			self :: $controller = new $name();
			if (self :: $controller -> init()) {
				self :: $controller -> run();
				return true;
			}
		} catch (Exception $e) {
			Debug :: showException($e);
		}
		return false;
	}

}