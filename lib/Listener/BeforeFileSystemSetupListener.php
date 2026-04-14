<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Files_ExcludeDirs\Listener;

use OC\Files\Filesystem;
use OCA\Files_ExcludeDirs\Wrapper\Exclude;
use OCP\AppFramework\Services\IAppConfig;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\BeforeFileSystemSetupEvent;
use OCP\Files\Storage\IStorage;

/**
 * @template-implements IEventListener<BeforeFileSystemSetupEvent>
 */
class BeforeFileSystemSetupListener implements IEventListener {
	public function __construct(
		private IAppConfig $config,
	) {
	}

	public function handle(Event $event): void {
		if (!$event instanceof BeforeFileSystemSetupEvent) {
			return;
		}

		Filesystem::addStorageWrapper('exclude', function ($mountPoint, IStorage $storage): Exclude {
			$exclude = $this->config->getAppValueArray('exclude', [".snapshot"]);
			return new Exclude(['storage' => $storage, 'exclude' => $exclude]);
		});
	}
}
