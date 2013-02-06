<?php

namespace Core
{
	class Autoloader
	{
		/**
		 * Namespaces collection
		 * @var array
		 */
		private static $namespaces = array();

		/**
		 * Register namespaces
		 * @param  array $namespaces
		 */
		public static function register_namespaces (Array $namespaces)
		{
			self::$namespaces = $namespaces;
		}

		/**
		 * Making the autoloading work
		 * @param  $class
		 */
		public static function dispatch ($class)
		{
			if (count (self::$namespaces))
			{
				foreach (self::$namespaces as $namespace => $path)
				{
					if (strstr ($class, $namespace))
					{
						require PACKAGES_PATH.$path; 
						return;
					}
				}
			}

			$class = str_replace ('\\', DS, strtolower ($class)).'.php';

			if (is_file (PACKAGES_PATH.$class))
			{
				require PACKAGES_PATH.$class;
			}
		}
	}

	// Register the autoloader
	spl_autoload_register (array ('Core\Autoloader', 'dispatch'));
}