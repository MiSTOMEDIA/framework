<?php

namespace Core
{
	abstract class Dispatcher
	{
		public static function run()
		{
			return \Core\Router::match ($_GET ['uri']);
		}
	}
}