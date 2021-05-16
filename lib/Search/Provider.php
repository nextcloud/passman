<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
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

namespace OCA\Passman\Search;

use OCA\Passman\AppInfo\Application;
use OCA\Passman\Db\CredentialMapper;
use OCA\Passman\Db\VaultMapper;
use OCA\Passman\Service\SettingsService;
use OCA\Passman\Service\VaultService;
use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IDBConnection;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use OCP\Search\SearchResultEntry;

class Provider implements IProvider {

	private IL10N $l10n;
	private IURLGenerator $urlGenerator;
	private IDBConnection $db;
	private SettingsService $settings;

	public function __construct(IL10N $l10n, IURLGenerator $urlGenerator, IDBConnection $db, SettingsService $settings) {
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
		$this->db = $db;
		$this->settings = $settings;
	}

	public function getId(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l10n->t('Passman');
	}

	public function getOrder(string $route, array $routeParameters): int {
		if (strpos($route, Application::APP_ID . '.') === 0) {
			// Active app, prefer my results
			return -1;
		}

		return 25;
	}

	public function search(IUser $user, ISearchQuery $query): SearchResult {
		$searchResultEntries = [];

		if ($this->settings->getAppSetting('enable_global_search', 0) === 1) {
			$VaultService = new VaultService(new VaultMapper($this->db, new Utils()));
			$Vaults = $VaultService->getByUser($user->getUID());
			$CredentialMapper = new CredentialMapper($this->db, new Utils());

			foreach ($Vaults as $Vault) {
				try {
					$Credentials = $CredentialMapper->getCredentialsByVaultId($Vault->getId(), $Vault->getUserId());

					foreach ($Credentials as $Credential) {
						if (strpos($Credential->getLabel(), $query->getTerm()) !== false) {
							try {
								$searchResultEntries[] = new SearchResultEntry(
									$this->urlGenerator->imagePath(Application::APP_ID, 'app.svg'),
									$Credential->getLabel(),
									\sprintf("Part of Passman vault %s", $Vault->getName()),
									$this->urlGenerator->linkToRoute('passman.page.index') . "#/vault/" . $Vault->getGuid() . "?show=" . $Credential->getGuid()
								);
							} catch (\Exception $e) {
							}
						}
					}
				} catch (DoesNotExistException $e) {
				} catch (MultipleObjectsReturnedException $e) {
				}
			}
		}

		return SearchResult::complete(
			$this->l10n->t(Application::APP_ID),
			$searchResultEntries
		);
	}
}
