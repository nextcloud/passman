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

namespace OCA\Passman\Service;

use OCA\Passman\BackupRestore\BackupArchive;
use OCA\Passman\BackupRestore\BackupManifest;
use OCA\Passman\BackupRestore\Restore\RestoreContext;
use OCA\Passman\BackupRestore\Restore\RestorePipeline;
use OCA\Passman\BackupRestore\Restore\RestorePreflight;
use OCA\Passman\BackupRestore\Restore\RestoreScopeCleaner;
use OCA\Passman\BackupRestore\RestoreResult;
use OCA\Passman\Exception\InvalidBackupException;
use OCP\IDBConnection;

/**
 * Imports a {@see BackupArchive} back into the database, inside a single transaction.
 *
 * A thin facade: {@see RestorePreflight} checks whether the artifact can be applied at all,
 * {@see RestoreScopeCleaner} deletes the artifact's scope first in replace mode, and
 * {@see RestorePipeline} then inserts/updates every section via {@see RestoreContext}, which
 * carries the mode plus the old-id -> this-instance-id maps every regenerated id is remapped onto.
 * Rows referencing a vault or credential which is neither part of the artifact nor present on this
 * instance are skipped with a warning (see {@see RestoreWarnings}), which is expected for the user
 * and vault scopes.
 *
 * A `portable` artifact holds data without the Nextcloud server side encryption layer, so it is
 * re-applied here with the key material of this instance, see {@see ServerEncryptionApplier}.
 * A `raw` artifact is inserted verbatim. The end to end encryption layer is never touched.
 */
readonly class RestoreService {

	public const MODE_REPLACE = 'replace';
	public const MODE_MERGE = 'merge';
	public const MODES = [self::MODE_REPLACE, self::MODE_MERGE];

	public function __construct(
		private RestorePreflight    $preflight,
		private RestoreScopeCleaner $cleaner,
		private RestorePipeline     $pipeline,
		private IDBConnection       $db,
	) {
	}

	public static function isValidMode(string $mode): bool {
		return in_array($mode, self::MODES, true);
	}

	/**
	 * Restores the given artifact. The scope and the encryption mode are read from
	 * its manifest, only the restore mode is chosen by the caller.
	 *
	 * @param string $mode one of self::MODE_*
	 * @param bool $force restore a raw artifact of a foreign instance anyway
	 * @return RestoreResult counts per section plus one warning per skipped row
	 * @throws \InvalidArgumentException on an unknown restore mode
	 * @throws InvalidBackupException when the artifact cannot be applied to this instance
	 * @throws \RuntimeException when a guid of the artifact is ambiguous on this instance
	 * @throws \OCP\DB\Exception on any database error, the whole restore is rolled back
	 */
	public function restore(BackupArchive $archive, string $mode, bool $force = false): RestoreResult {
		if (!self::isValidMode($mode)) {
			throw new \InvalidArgumentException('Unknown restore mode: "' . $mode . '"');
		}
		$this->preflight->assertRestorable($archive->manifest, $force);

		$context = new RestoreContext($mode, new RestoreResult());

		$this->db->beginTransaction();
		try {
			if ($mode === self::MODE_REPLACE) {
				$this->cleaner->clean($archive->manifest, $context->result);
			}
			$this->pipeline->run($archive, $context);

			$this->db->commit();
		} catch (\Throwable $e) {
			$this->db->rollBack();
			throw $e;
		}

		return $context->result;
	}

	/**
	 * Limitations of restoring the given artifact which the caller should present to the admin.
	 *
	 * @param string $mode one of self::MODE_*
	 * @return string[]
	 */
	public function getCaveats(BackupManifest $manifest, string $mode): array {
		return $this->preflight->caveats($manifest, $mode);
	}
}
