<?php
/**
 * Nextcloud - Passman
 *
 * @copyright 2026 Timo Triebensky (timo@binsky.org)
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

declare(strict_types=1);

namespace OCA\Passman\BackupRestore\Restore;

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\Exception\InvalidBackupException;

/**
 * Restores one section of a {@see BackupArchive} into the database.
 * {@see RestorePipeline} runs one implementation per {@see BackupArchive::SECTION_*} in dependency order.
 */
interface SectionRestorer {

	/**
	 * @return string one of BackupArchive::SECTION_*
	 */
	public function section(): string;

	/**
	 * @throws InvalidBackupException when a row of the section does not match its entity
	 * @throws \RuntimeException when a guid referenced by a row is ambiguous on this instance
	 */
	public function restore(BackupArchive $archive, RestoreContext $context): void;
}
