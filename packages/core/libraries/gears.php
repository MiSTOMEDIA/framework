<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/gears
 */

namespace Core\Libraries;

use \Exception;

/**
 * A template engine that will display a specific template within the templates directory.
 * The template can be bound with variables that are passed into the engine from PHP,
 * wrap itself with layout templates, and open templates within templates.
 *
 * @version	4.0.0
 * @package	mjohnson.gears
 */
class Gears {

	/**
	 * Is caching enabled?
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_cache = false;

	/**
	 * How long the cache should last until being overwritten.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_cacheDuration = '+1 day';

	/**
	 * Path to the cached files, relative to the given CSS path.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_cachePath;

	/**
	 * The rendered inner content to be used in the layout.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_content;

	/**
	 * Extension of the template files.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_ext = 'tpl';

	/**
	 * Name of the current layout.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_layout;

	/**
	 * Template directory.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_path;

	/**
	 * Array of binded template variables.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_variables = array();

	/**
	 * Configure the templates path and file extension.
	 *
	 * @access public
	 * @param string $path
	 * @param string $ext
	 */
	// public function __construct($path, $ext = 'tpl') {
	// 	$path = rtrim(str_replace('\\', '/', $path), '/') .'/';

	// 	if (!$ext) {
	// 		$ext = 'tpl';
	// 	}

	// 	$this->_ext = trim($ext, '.');
	// 	$this->_path = $path;
	// }





	public function setPath ($path, $ext = 'tpl')
	{
		$path = rtrim(str_replace('\\', '/', $path), '/') .'/';

		if (!$ext) {
			$ext = 'tpl';
		}

		$this->_ext = trim($ext, '.');
		$this->_path = $path;
	}






	/**
	 * Binds variables to all the templates.
	 *
	 * @access public
	 * @param array|string $variable
	 * @param string $value
	 * @return Gears
	 * @chainable
	 */
	public function bind($variable, $value = null) {
		if (is_array($variable)) {
			foreach ($variable as $var => $value) {
				$this->bind($var, $value);
			}
		} else {
			$variable = preg_replace('/[^_a-zA-Z0-9]/i', '', $variable);

			if (is_numeric($variable)) {
				$variable = '_' . $variable;
			}

			$this->_variables[$variable] = $value;
		}

		return $this;
	}

	/**
	 * Checks for a valid template extension and that the file exists.
	 *
	 * @access public
	 * @param string $tpl
	 * @return string
	 * @throws Exception
	 */
	public function checkPath($tpl) {
		if (substr($tpl, -(strlen($this->_ext) + 1)) !== '.' . $this->_ext) {
			$tpl .= '.' . $this->_ext;
		}

		$path = str_replace($this->_path, '', trim($tpl, '/'));

		if (!is_file($this->_path . $path)) {
			throw new Exception(sprintf('The template "%s" does not exist.', $tpl));
		}

		return $path;
	}

	/**
	 * Displays the chosen template and its layout; will return a cached version if it exists.
	 *
	 * @access public
	 * @param string $tpl
	 * @param string $key
	 * @return mixed
	 */
	public function display($tpl, $key = null) {
		$path = $this->_path . $this->checkPath($tpl);

		// Return the cache if it exists
		if ($cache = $this->isCached($key)) {
			return $cache;
		}

		$this->_content = $this->_render($path);

		// Render layout if it exists
		if ($this->_layout) {
			$this->_content = $this->_render($this->_path . $this->_layout);
		}

		// Cache the rendered page
		if ($this->_cache && $key) {
			$this->_cache($key, $this->getContent());
		}

		return $this->_content;
	}

	/**
	 * Delete all cached files.
	 *
	 * @access public
	 * @return void
	 */
	public function flush() {
		if (!$this->_cache) {
			return;
		}

		if ($dh = opendir($this->_cachePath)) {
			while (($file = readdir($dh)) !== false) {
				if ($file !== '.' && $file !== '..') {
					@unlink($this->_cachePath . $file);
				}
			}
		}
	}

	/**
	 * Return the rendered content.
	 *
	 * @access public
	 * @return string
	 */
	public function getContent() {
		return $this->_content;
	}

	/**
	 * Check to see if the templates are cached and is within the cache duration.
	 *
	 * @access protected
	 * @param string $key
	 * @return string|boolean
	 */
	public function isCached($key) {
		if (!$this->_cache || !$key) {
			return false;
		}

		$path = $this->_cachePath . $key;

		if (file_exists($path)) {
			list($timestamp, $content) = explode("\n", file_get_contents($path));

			if ($timestamp >= time()) {
				$this->_content = $content;

				return $content;
			}
		}

		return false;
	}

	/**
	 * Include a template within another template. Can pass variables into its own protected scope.
	 *
	 * @access public
	 * @param string $tpl
	 * @param array $variables
	 * @param array|string $cache
	 * @return string
	 */
	public function open($tpl, array $variables = array(), $cache = array()) {
		if ($cache === true) {
			$cache = $tpl;
		}

		if (is_string($cache)) {
			$cache = array('key' => $cache);
		}

		$cache = $cache + array(
			'key' => null,
			'duration' => $this->_cacheDuration
		);

		if ($content = $this->isCached($cache['key'])) {
			return $content;
		}

		$content = $this->_render($this->_path . $this->checkPath($tpl), $variables);

		if ($this->_cache && $cache['key']) {
			$this->_cache($cache['key'], $content);
		}

		return $content;
	}

	/**
	 * Set the cache path and duration.
	 *
	 * @access public
	 * @param string $path
	 * @param string $duration
	 * @return Gears
	 * @chainable
	 */
	public function setCaching($path, $duration = '+1 day') {
		if (!$path) {
			$path = $this->_path . '_cache/';
		}

		$path = trim(str_replace('\\', '/', $path), '/') . '/';

		$this->_cache = true;
		$this->_cachePath = $path;
		$this->_cacheDuration = $duration;

		return $this;
	}

	/**
	 * Set the current layout to be used.
	 *
	 * @access public
	 * @param string $tpl
	 * @return Gears
	 * @chainable
	 */
	public function setLayout($tpl) {
		if ($path = $this->checkPath($tpl)) {
			$this->_layout = $path;
		}

		return $this;
	}

	/**
	 * Creates a cached file of the template.
	 *
	 * @access protected
	 * @param string $name
	 * @param string $content
	 * @return void
	 */
	protected function _cache($name, $content) {
		if (!$this->_cache) {
			return;
		}

		$name = trim($name, '/');
		$path = $this->_cachePath . $name;
		$dir = dirname($path);

		// Create folders if they do not exist
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		if (!is_writeable($dir)) {
			chmod($dir, 0777);
		}

		$duration = is_numeric($this->_cacheDuration) ? $this->_cacheDuration : strtotime($this->_cacheDuration);
		$cache = $duration ."\n". $this->_compress($content);

		file_put_contents($path, $cache);
	}

	/**
	 * Compress the template by removing white space.
	 *
	 * @access protected
	 * @param string $content
	 * @return string
	 */
	protected function _compress($content) {
		$content = str_replace(array("\r\n", "\r", "\n", "\t", '/\s\s+/', '  ', '   '), '', $content);
		$content = preg_replace('/<!--(.*)-->/Uis', '', $content);

		return $content;
	}

	/**
	 * Render the template and extract the variables using output buffering.
	 *
	 * @access protected
	 * @param string $path
	 * @param array $variables
	 * @return string
	 */
	protected function _render($path, array $variables = array()) {
		$variables = $variables + $this->_variables;
		extract($variables, EXTR_SKIP);

		ob_start();
		include $path;
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

}