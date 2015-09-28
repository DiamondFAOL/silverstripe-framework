<?php

namespace SilverStripe\Filesystem\Storage;

use Config;

/**
 * Basic filename renamer
 *
 * @package framework
 * @subpackage filesystem
 */
class DefaultAssetNameGenerator implements AssetNameGenerator {

	/**
	 * A prefix for the version number added to an uploaded file
	 * when a file with the same name already exists.
	 * Example using no prefix: IMG001.jpg becomes IMG2.jpg
	 * Example using '-v' prefix: IMG001.jpg becomes IMG001-v2.jpg
	 *
	 * @config
	 * @var string
	 */
	private static $version_prefix = '-v';

	/**
	 * Original filename
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 * Directory
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * Name without extension or directory
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Extension (including leading period)
	 *
	 * @var string
	 */
	protected $extension;

	/**
	 * Next version number to suggest
	 *
	 * @var int
	 */
	protected $version;

	/**
	 * Maximum number to suggest
	 *
	 * @var int
	 */
	protected $max = 100;

	/**
	 * First version
	 *
	 * @var int
	 */
	protected $first = null;

	public function __construct($filename) {
		$this->filename = $filename;
		$this->directory = ltrim(dirname($filename), '.');
		$name = basename($this->filename);
		if(($pos = strpos($name, '.')) !== false) {
			$this->extension = substr($name, $pos);
			$name = substr($name, 0, $pos);
		} else {
			$this->extension = null;
		}

		// Extract version prefix if already applied to this file
		$pattern = '/^(?<name>.+)' . preg_quote($this->getPrefix()) . '(?<version>[0-9]+)$/';
		if(preg_match($pattern, $name, $matches)) {
			$this->first = $matches['version'] + 1;
			$this->name = $matches['name'];
		} else {
			$this->first = 1;
			$this->name = $name;
		}

		$this->rewind();
	}

	/**
	 * Get numeric prefix
	 *
	 * @return string
	 */
	protected function getPrefix() {
		return Config::inst()->get(__CLASS__, 'version_prefix');
	}

	public function current() {
		$version = $this->version;
		
		// Initially suggest original name
		if($version === 1) {
			return $this->filename;
		}

		// If there are more than $this->max files we need a new scheme
		if($version >= $this->max) {
			$version = substr(md5(time()), 0, 10);
		}

		// Build next name
		$filename = $this->name . $this->getPrefix() . $version . $this->extension;
		if($this->directory) {
			$filename = $this->directory . DIRECTORY_SEPARATOR . $filename;
		}
		return $filename;
	}

	public function key() {
		return $this->version;
	}

	public function next() {
		$this->version++;
	}

	public function rewind() {
		$this->version = $this->first;
	}

	public function valid() {
		return $this->version <= $this->max;
	}

}