<?php

namespace Core
{
	class Router
	{
		private static $routes = array();

		public static function add_route ($route, $callback)
		{
			if (! array_key_exists ($route, self::$routes))
			{
				self::$routes [$route] = $callback;
			}
		}

		public static function match ($uri)
		{
			if (array_key_exists ($uri, self::$routes))
			{
				list ($controller, $method) = explode ('::', self::$routes [$uri]);

				$controller = str_replace ('.', '\\', $controller);
				$controller = new $controller;
				
				return $controller->$method();
			}
		}

		public static function routes()
		{
			print_r (self::$routes);
		}
	}
}