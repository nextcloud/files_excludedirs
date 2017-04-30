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

use OC\Files\Filesystem;
use OC\Files\Storage\Storage;
use OCP\IConfig;

class Manager {
	/** @var IConfig */
	private $config;

	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	public function setupStorageWrapper() {
		Filesystem::addStorageWrapper('exclude', function ($mountPoint, Storage $storage) {
			$exclude = json_decode(
				$this->config->getAppValue('files_excludedirs', 'exclude', '[".snapshot"]')
			);
			return new Exclude(['storage' => $storage, 'exclude' => $exclude]);
		});
	}
}
