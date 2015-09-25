<?php

namespace App;

use Models;

/*

	User class - for session processing

	basic usage:

		User :: startSession();
		User :: signIn(email, password, remember);
		User :: signOut();

*/

class User {

	public static
		/**
		 * @var user id
		 */
		$id = null,

		/**
		 * @var user name
		 */
		$name = null,

		/**
		 * @var user model, retrieved from session
		 */
		$model = null,

		/**
		 * @var signed flag
		 */
		$signed = false;


	/**
	 * Start session and restore user model by session or cookies
	 *
	 * @return \Models\User
	 */
	public static function startSession()
	{
		if (session_id() === '') {
			session_start();
			self :: signBySession() or self :: signByCookies();
			if (self :: $signed and self :: $model) {
				return self :: $model;
			}
		}
		return null;
	}


	/**
	 * Try restore user by session
	 * Saves lastVisit time of user
	 *
	 * @return boolean
	 */
	private static function signBySession()
	{
		if (session_id() === '')
			return false;

		if ($savedUser = Request :: session('logined_user')) {
			self :: $id = $savedUser['id'];
			self :: $name = $savedUser['name'];
			self :: $model = $user = new Models\User($savedUser);

			$user -> lastVisit = Timer :: now();
			$user -> save(['lastVisit']);

			return self :: $signed = true;
		}

		return false;
	}


	/**
	 * Try get saved user by cookie
	 * Saves lastVisit time of user
	 *
	 * @return boolean
	 */
	private static function signByCookies()
	{
		if (!$hash = Request :: cookie('user_key'))
			return false;

		if ($user = Models\User :: findByCookie($hash, Request :: clientIP())) {
			$_SESSION['logined_user'] = get_object_vars($user);
			self :: $model = $user;
			self :: $id = $user -> id;
			self :: $name = $user -> name;

			$user -> lastVisit = Timer :: now();
			$user -> save(['lastVisit']);

			return self :: $signed = true;
		}
		return false;
	}


	/**
	 * Try sign in user by $email, $password
	 * If user found in database, creates session and cookie,
	 * saves user lastIP, cookie, lastVisit time
	 *
	 * @param string $email
	 * @param string $password
	 * @return boolean
	 */
	public static function signIn($email, $password)
	{
		if (!$user = Models\User :: findByEmailPassword($email, $password))
			return false;

		self :: $id = $user -> id;
		self :: $name = $user -> name;
		self :: $model = $user;
		self :: $signed = true;
		self :: saveUserVisit($user);

		Request :: setSession('logined_user', $user -> fields());
		Request :: setCookie("user_key", $user -> cookie);
		return true;
	}


	/**
	 * Store users login data - ip and visit time
	 *
	 * @param \Models\User $user
	 * @return void
	 */
	private static function saveUserVisit(Models\User $user)
	{
		$user -> lastVisit = Timer :: now();
		$user -> lastIP = Request :: clientIP();
		$user -> save(['lastVisit', 'lastIP', 'cookie']);
	}


	/**
	 * Sign out user.
	 * Clears session and cookie
	 *
	 * @return boolean
	 */
	public static function signOut()
	{
		self :: $id = 0;
		self :: $name = '';
		self :: $model = null;
		self :: $signed = false;

		Request :: setCookie("user_key", null);
		if (session_id() !== '')
			session_destroy();

		return true;
	}


	/**
	 * This must be called after update current user model
	 *
	 * @return void
	 */
	public static function updateSession($user)
	{
		if (self :: $signed) {
			self :: $id = $user -> id;
			self :: $name = $user -> name;
			self :: $model = $user;
			Request :: setSession('logined_user', $user -> fields());
		}
	}


}