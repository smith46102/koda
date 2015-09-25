<?php

namespace Controllers;

use
	\App\View,
	\App\Request,
	\App\Response;

/*

	ErrorController
	shows 404 error page

*/

class Error extends \App\Controller {


	// shows 404 page
	public function init($options = null)
	{
		header('HTTP/1.1 404 Not Found');
		return true;
	}


	// render 404 page
	public function run()
	{
		View :: render('404');
	}


}