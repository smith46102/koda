<?php

namespace Controllers;

use
	\App\User,
	\App\View,
	\App\Mysql,
	\App\Request,
	\App\Response,
	\Modules;

/*

	LoginController
	serves authentication commands

*/

class Login extends \App\Controller {

	public
		$email = '',
		$password = '',
		$error = null;


	// checks on auth command
	public function init()
	{
		$command = _array($this -> path, 0);

		if ($command == 'signin') {
			$this -> signIn();
		} elseif ($command == 'signout') {
			$this -> signOut();
		}

		return true;
	}


	// renders login template
	public function run()
	{
		View :: set($this -> get(['login', 'password', 'error']));
		View :: render('login');
	}


	// makes `sign in` command
	private function signIn()
	{
		Mysql :: connect();
		$this -> email = Request :: post('email', '');
		$this -> password = Request :: post('password', '');

		if (User :: signIn($this -> email, $this -> password)) {
			Response :: redirect('/');
		} else {
			$this -> error = 'wrong';
		}

		return false;
	}


	// makes `sign out` command
	private function signOut()
	{
		if (User :: signOut()) {
			Response :: redirect('/login');
		}
		return true;
	}


}