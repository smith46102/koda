<?php

namespace Controllers;

use
	\App\User,
	\App\Response,
	\App\View,
	\App\Assets,
	\Modules;

/*
	PageController
	shows index template, for initialize web-app
*/

class Page extends \App\Controller {


	public function init()
	{
		if (!User :: $signed) {
			Response :: redirect('/login');
		}
		return true;
	}


	public function run()
	{
		Assets\Less :: compile('index.less', 'style.css');
		Assets\Javascript :: compile('gui/root.js', 'gui_min.js');
		Assets\Javascript :: compile('interface/root.js', 'interface_min.js');
		Assets\Javascript :: compile('layouts/root.js', 'layouts_min.js');

		$sidebarMod = new Modules\Sidebar\Sidebar();
		$sidebar = $sidebarMod -> render();
		$user = User :: $model;

		View :: set(compact('sidebar', 'user'));
		View :: render('html');
	}


}