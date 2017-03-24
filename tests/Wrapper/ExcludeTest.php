<?php
/**
 * @copyright Copyright (c) 2017 Robin Appelman <robin@icewind.nl>
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

namespace OCA\Files_ExcludeDirs\Test\Wrapper;

use OC\Files\Storage\Local;
use OC\Files\Storage\Temporary;
use OCA\Files_ExcludeDirs\Wrapper\Exclude;
use Test\TestCase;

\OC_App::loadApp('files_excludedirs');

class ExcludeTest extends TestCase {
	/** @var Local */
	private $sourceStorage;

	protected function setUp() {
		parent::setUp();

		$this->sourceStorage = new Temporary();
	}

	private function setupParentFolders($path) {
		$parts = explode('/', $path);
		array_pop($parts);
		$subPath = '';
		foreach ($parts as $part) {
			$subPath .= $part;
			$this->sourceStorage->mkdir($subPath);
			$subPath .= '/';
		}
	}

	public function testFileExistsProvider() {
		return [
			['foo', [], true],
			['foo', ['foo'], false],
			['bar', ['foo'], true],
			['bar/foo', ['foo'], false],
			['bar/foobar', ['foo'], true],
			['foo/foobar', ['foo'], false],
			['bar/foobar', ['foo*'], false],
			['bar/foobar', ['/foo*'], true],
			['bar/foo', ['bar/*'], false],
			['bar/foo/asd', ['bar/*'], true],
			['bar/foo/asd', ['bar/*/asd'], false],
			['bar/foo/qwerty/asd', ['bar/*/asd'], true],
			['bar/foo/qwerty/asd', ['bar/**/asd'], false],
		];
	}

	/**
	 * @dataProvider testFileExistsProvider
	 *
	 * @param string $path
	 * @param string[] $exclude
	 * @param bool $expected
	 */
	public function testFileExists($path, $exclude, $expected) {
		$this->setupParentFolders($path);
		$this->sourceStorage->file_put_contents($path, 'dummy');


		$storage = new Exclude([
			'storage' => $this->sourceStorage,
			'exclude' => $exclude
		]);

		$this->assertEquals($expected, $storage->file_exists($path));
	}

	public function testOpenDir() {
		$this->sourceStorage->mkdir('root');
		$this->sourceStorage->file_put_contents('root/asd', '');
		$this->sourceStorage->file_put_contents('root/bar', '');
		$this->sourceStorage->file_put_contents('root/foo', '');

		$storage = new Exclude([
			'storage' => $this->sourceStorage,
			'exclude' => [
				'asd',
				'root/bar',
				'folder/foo'
			]
		]);

		$dh = $storage->opendir('root');

		$content = [];
		while ($file = readdir($dh)) {
			$content[] = $file;
		}

		$this->assertEquals(['foo'], $content);
	}
}
