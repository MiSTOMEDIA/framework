<?php

namespace Pilot\Controllers
{
	class Home extends \Core\Controller
	{
		/**
		 * The index method
		 * @return View
		 */
		public function index()
		{
			return $this->view->render ('Pilot.Views.Home::index', array (
				'message' => 'Hello'
			));
		}
	}
}