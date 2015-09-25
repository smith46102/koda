<?php

require_once 'engine/utils.php';
require_once 'engine/autoload.php';
Autoload :: init('config/autoload.php');

use
	App\Config,
	App\Request,
	App\User,
	App\Route,
	App\View,
	App\Timer,
	App\Debug,
	App\Mysql,
	App\Response;

try {

	Config :: load('config/settings.php');
	Config :: applyHostSettings('config/hosts.php');

	Mysql :: setHost(Config :: get('dbConnection'));

	Timer :: start();
	User :: startSession();
	Request :: parse();
	Route :: add([
		'/dumper' => 'dumper',
		'/module' => 'module',
		'/login' => 'login',
		'/tasks' => 'page',
		'/profile' => 'page',
		'/projects' => 'page',
		'/workspace' => 'page',
		'/404' => 'error',
		'/' => 'page'
	]);
	Route :: go();

	Timer :: showExecutionTime();
	Mysql :: showResources();
	Response :: out();

} catch (\Exception $e) {

	Debug :: showException($e);

}