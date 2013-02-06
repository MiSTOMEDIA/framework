<?php

namespace Core
{
	class View
	{
		/**
		 * The view instance
		 * @var Gears
		 */
		protected $view;

		/**
		 * Assign a new instance of Gears to the view property
		 */
		public function __construct()
		{
			$this->view = new \Core\Libraries\Gears;
		}

		/**
		 * Render a view via the Gears template engine
		 * @param  string $view
		 * @param  array  $data
		 * @return Gears
		 */
		public function render ($view, $data = array())
		{
			list ($path, $view) = explode ('::', $view);

			$path = PACKAGES_PATH.str_replace ('.', DS, strtolower ($path)).DS;
			$view = ! strstr ($view, '.tpl') ? $view.'.tpl' : $view;

			$this->view->setPath ($path);
			
			if (count ($data))
			{
				$this->view->bind ($data);
			}

			return $this->view->display ($view);
		}
	}
}