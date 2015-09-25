<?php

namespace App;

/*

	View class - setup variables and template rendering

*/

class View {

	private static
		/**
		 * @var global view vars
		 */
		$vars = [];


	/**
	 * Set View global variables
	 *
	 * @param array $vars
	 * @return void
	 */
	public static function set(array $vars)
	{
		self :: $vars = array_merge(self :: $vars, $vars);
	}


	/**
	 * Get View global variable
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function get($name)
	{
		return isset(self :: $vars[$name]) ? self :: $vars[$name] : null;
	}


	/**
	 * Render template, and add render result to Response output
	 *
	 * @param string $template
	 * @return void
	 */
	public static function render($template)
	{
		$template = new Template($template);
		Response :: add($template -> render());
	}


	/**
	 * Render template to buffer, and return render result
	 *
	 * @param string $template
	 * @return string
	 */
	public static function renderBuf($template)
	{
		$template = new Template($template);
		return $template -> render();
	}


	/**
	 * Render template as JSON, add render result to Response output
	 * Set Response[status] as $status
	 *
	 * @param string $name
	 * @param string $status
	 * @return void
	 */
	public static function renderJson($template, $status = 'ok', $data = null)
	{
		$template = new Template($template);
		$output = [
			'status' => $status,
			'data' => $template -> render()
		];
		if (is_array($data)) {
			$output = array_merge($output, $data);
		}
		Response :: setJson();
		Response :: add($output);
	}

}