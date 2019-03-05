<?php
/**
 * @copyright 2016 Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Files_ExcludeDirs\Wrapper;

use Icewind\Streams\IteratorDirectory;
use OC\Files\Storage\Wrapper\Wrapper;
use Webmozart\Glob\Glob;

class Exclude extends Wrapper {
	/**
	 * @var string[] Directories to exclude
	 */
	private $exclude;

	/**
	 * @param array $parameters
	 */
	public function __construct($parameters) {
		parent::__construct($parameters);
		$this->exclude = $parameters['exclude'];
	}

	/**
	 * Check if the path contains an ignored direcotry
	 *
	 * @param string $path
	 * @return bool
	 */
	private function excludedPath($path) {
		if ($path === '') {
			return false;
		}

		foreach ($this->exclude as $rule) {
			// glob requires all paths to be absolute so we put /'s in front of them
			if (strpos($rule, '/') !== false) {
				$rule = '/' . rtrim($rule, '/');
				if(Glob::match('/' . $path, $rule)) {
					return true;
				}
			} else {
				$parts = explode('/', $path);
				$rule = '/' . $rule;
				foreach ($parts as $part) {
					if (Glob::match('/' . $part, $rule)) {
						return true;
					}
				}
			}
		}

		return false;
	}

	public function file_exists($path) {
		if ($this->excludedPath($path)) {
			return false;
		}

		return parent::file_exists($path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function opendir($path) {
		$directoryIterator = $this->iterateDirectory($path);

		if ($directoryIterator) {
			$filteredDirectory = new \CallbackFilterIterator($directoryIterator, function ($name) use ($path) {
				return !$this->excludedPath($path . '/' . $name);
			});
			$filteredDirectory->rewind();
			return IteratorDirectory::wrap($filteredDirectory);
		}

		return false;
	}

	private function iterateDirectory($path) {
		if ($this->excludedPath($path)) {
			return false;
		}

		$handle = $this->storage->opendir($path);
		while ($file = readdir($handle)) {
			if ($file !== '.' && $file !== '..') {
				yield $file;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * Todo: throw forbiddenexception??
	 */
	public function getMetaData($path) {
		if ($this->excludedPath($path)) {
			return null;
		}
		return $this->getWrapperStorage()->getMetaData($path);
	}
}
