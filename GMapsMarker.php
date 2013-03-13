<?php

namespace Xiphe\GMaps;

/**
 * A small API that creates custom markers for Google Maps
 *
 * @author    Hannes Diercks <info@xiphe.net>
 * @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE
 * @link      https://github.com/Xiphe/GMapsMakerCreator/
 */
class Marker {
	const DS = DIRECTORY_SEPARATOR;

	public static $basePath = './';
	public static $cacheLiveTime = 2592000; // 30 Days
	public static $checkCacheEvery = 604800; // 7 Days
	private static $_checkedCache = false;
	private static $_basePathIsAbsolute = false;

	private $_defaults = array(
		'content' => '•',
		'font-weight' => 'normal',
		'color' => '000000',
		'border-color' => '9D3E38',
		'background-color' => 'FE7D72',
		'font-size' => 17,
		'left' => 6,
		'top' => 16,
		'2x' => 0
	);

	public $settings = array();

	public function __construct($options = array())
	{
		self::_makeAbsoluteBasePath();
		foreach ($this->_defaults as $key => $value) {
			if (!empty($options[$key])) {
				$value = $options[$key];
			} elseif (!empty($_GET[$key])) {
				$normalizer = array($this, 'normalize_' . str_replace('-', '_', $key));
				if (is_callable($normalizer)) {
					$value = call_user_func($normalizer, $_GET[$key]);
				} else {
					$value = $_GET[$key];
				}
			}
			
			$this->settings[$key] = $value;
		}
	}

	public function normalize_content($value)
	{
		if (!is_string($value)) {
			return 'A';
		}

		if (strlen($value) > 2) {
			return substr($value, 0, 2);
		}
	}

	public function normalize_font_weight($value)
	{
		if (!in_array($value, array('normal', 'bold'))) {
			$value = 'normal';
		}

		return $value;
	}

	public function normalize_color($value, $default = false)
	{
		$default = $default ? $default : $this->_defaults['color'];

		if (!is_string($value)) {
			return $default;
		}

		$value = preg_replace('/[^0-9A-F]/', "", strtoupper($value));

		if (!in_array(strlen($value), array(3, 6))) {
			return $default;
		}

		return $value;
	}

	public function normalize_border_color($value)
	{
		return $this->normalize_color($value, $this->_defaults['border-color']);
	}

	public function normalize_background_color($value)
	{
		return $this->normalize_color($value, $this->_defaults['background-color']);
	}

	public function normalize_font_size($value)
	{
		$value = intval($value);
		if ($value > 20 || $value < 2) {
			$value = $this->_defaults['font-size'];
		}

		return $value;
	}

	public function normalize_top($value)
	{
		$value = intval($value);
		if ($value > 90 || $value < -30) {
			$value = $this->_defaults['top'];
		}

		return $value;
	}

	public function normalize_left($value)
	{
		return $this->normalize_top($value);
	}

	public function normalize_2x($value)
	{
		return (boolean) $value;
	}

	public function create()
	{
		ksort($this->settings);
		$serial = md5(serialize($this->settings));
		$file = self::$basePath . 'cache' . self::DS . $serial . '.png';

		if (!file_exists($file)) {
			$image = $this->getBackground();
			$border = $this->getBorder();
			imagecopy($image, $border, 0, 0, 0, 0, 19, 31);

			imagettftext(
				$image,
				$this->settings['font-size'],
				0,
				$this->settings['left'],
				$this->settings['top'],
				$this->getTextColorFor($image),
				$this->getFontFile(),
				$this->getText()
			);

			imagepng($image, $file);
		} else {
			touch($file);
		}

		return $file;
	}

	public function getText()
	{
	    return preg_replace('~^(&([a-zA-Z0-9]);)~', htmlentities('${1}'), $this->settings['content']);
	}

	public function getTextColorFor($img)
	{
		$color = $this->hexToRGB($this->settings['color']);
		return ImageColorAllocate($img, $color[0], $color[1], $color[2]);
	}

	public function getFontFile()
	{
		return self::$basePath . 'res' . self::DS . 'fonts' . self::DS . 'SourceSansPro-' . strtolower($this->settings['font-weight']) . '.ttf';
	}

	public function getBackground()
	{
		return $this->getImageInColor(
			self::$basePath . 'res' . self::DS . 'img' . self::DS . 'background.png',
			$this->hexToRGB($this->settings['background-color'])
		);
	}

	public function getBorder()
	{
		return $this->getImageInColor(
			self::$basePath . 'res' . self::DS . 'img' . self::DS . 'border.png',
			$this->hexToRGB($this->settings['border-color'])
		);
	}

	public function __toString()
	{
		header('Content-Type: image/png');
		$file = $this->create();
		$expires = filemtime($file) + self::$cacheLiveTime;
		header("Expires: " . date(DATE_RFC1123, $expires));
		echo file_get_contents($file);
		exit();
	}

	public function getMarkerFile($onlyFilename = false)
	{
		$file = $this->create();
		return !$onlyFilename ? $file : basename($file);
	}

	/* http://snipplr.com/view/4621/ */
	public function hexToRGB($hex)
	{
		$color = array();

		if(strlen($hex) == 3) {
			$color[] = hexdec(substr($hex, 0, 1) . $r);
			$color[] = hexdec(substr($hex, 1, 1) . $g);
			$color[] = hexdec(substr($hex, 2, 1) . $b);
		}
		else if(strlen($hex) == 6) {
			$color[] = hexdec(substr($hex, 0, 2));
			$color[] = hexdec(substr($hex, 2, 2));
			$color[] = hexdec(substr($hex, 4, 2));
		}
		 
		return $color;
	}

	/* Inspired by http://stackoverflow.com/questions/5753388/php-gd-color-replacement-with-alpha-gives-images-a-border */
	public function getImageInColor($source, $newColor)
	{
		$source = imagecreatefrompng($source);
	    $w = imagesx($source);
	    $h = imagesy($source);

	    $target = imagecreatetruecolor($w, $h);
	    $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
    	imagefill($target, 0, 0, $transparent);

	    // Work through pixels
	    for($y=0;$y<$h;$y++) {
	        for($x=0;$x<$w;$x++) {
	            $rgb = imagecolorsforindex($source, imagecolorat($source, $x, $y));
	            $pixelColor = imagecolorallocatealpha($target, $newColor[0], $newColor[1], $newColor[2], $rgb['alpha']);
	            imagesetpixel($target, $x, $y, $pixelColor);
	        }
	    }
	    imageSaveAlpha($target, true);

	    return $target;
	}

	private static function _makeAbsoluteBasePath()
	{
		if (!self::$_basePathIsAbsolute) {
			self::$_basePathIsAbsolute = true;
			self::$basePath = realpath(dirname(__FILE__) . self::DS . self::$basePath) . self::DS;
		}
	}

	private static function _ensureFileExists($file)
	{
		if (!file_exists($file)) {
			$path = dirname($file);
			if (!is_dir($path)) {
				@mkdir($path, 0777, true);
			}

			$handle = @fopen($file, 'w');
			if ($handle) {
				@fclose($handle);
			}
			unset($handle);
			if (!file_exists($file) || !is_writable($file)) {
				throw new Exception("File does not exist or is not writable: $file");
			}
		}
	}

	private static function _checkCache()
	{
		$cacheFolder = self::$basePath . 'cache' . self::DS;
		$flag = $cacheFolder . '.gmapsmaker';
		$check = false;
		if (!file_exists($flag)) {
			$check = true;
			self::_ensureFileExists($flag);
		}

		if (!$check && self::$checkCacheEvery + filemtime($flag) > time()) {
			return;
		}

		foreach (glob($cacheFolder . '*') as $file) {
			if (filemtime($file) + self::$cacheLiveTime < time()) {
				unlink($file);
			}
		}
		touch($flag);
	}

	public function __destruct()
	{
		if (!self::$_checkedCache) {
			self::$_checkedCache = true;
			self::_checkCache();
		}
	}
}

class Exception extends \Exception {};