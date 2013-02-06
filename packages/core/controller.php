<?php

namespace Core
{
	class Controller
	{
		/**
		 * Our view property
		 * @var ViewEngine
		 */
		protected $view;

		/**
		 * Assign a new View to the view property
		 */
		public function __construct()
		{
			$this->view = new View();
		}
	}
}