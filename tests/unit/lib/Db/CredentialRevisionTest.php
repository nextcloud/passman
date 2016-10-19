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

use \OCA\Passman\Db\CredentialRevision;

/**
 * @coversDefaultClass \OCA\Passman\Db\CredentialRevision
 */
class CredentialRevisionTest extends PHPUnit_Framework_TestCase {
	CONST TEST_DATA = [
		'id'			=> 30,
		'guid'			=> 'FA8D80E0-90AB-4D7A-9937-913F486C24EA',
		'credential_id'	=> 5,
		'user_id'		=> 'WolFi',
		'created'		=> 1475964600,
		'credential_data' => 'eyJjcmVkZW50aWFsX2lkIjo0LCJndWlkIjoiODMxRDM0RkMtRTdDMC00NTQxLUJENjgtNTA3NzQ3ODI0MTkyIiwidXNlcl9pZCI6IndvbGZpIiwidmF1bHRfaWQiOjEsImxhYmVsIjoidGVzdDIiLCJkZXNjcmlwdGlvbiI6ImV5SnBkaUk2SWxoVVFuWlRjRE5oSzFSMU4zbFdkVlJNY0dFeGJVRWlMQ0oySWpveExDSnBkR1Z5SWpveE1EQXdMQ0pyY3lJNk1qVTJMQ0owY3lJNk5qUXNJbTF2WkdVaU9pSmpZMjBpTENKaFpHRjBZU0k2SWlJc0ltTnBjR2hsY2lJNkltRmxjeUlzSW5OaGJIUWlPaUkzWnpkSE1EaHpOM0ZJV1NJc0ltTjBJam9pYVM5TGFIRkhVVEJUZW5WNmRrb3JlQ0o5IiwiY3JlYXRlZCI6MTQ3NTc5NDIxMiwiY2hhbmdlZCI6MTQ3NTc5NDIxMiwidGFncyI6ImV5SnBkaUk2SW1ZeGRHNVdRVTlZWTJNM05uZGlUVWRuT1VjMUszY2lMQ0oySWpveExDSnBkR1Z5SWpveE1EQXdMQ0pyY3lJNk1qVTJMQ0owY3lJNk5qUXNJbTF2WkdVaU9pSmpZMjBpTENKaFpHRjBZU0k2SWlJc0ltTnBjR2hsY2lJNkltRmxjeUlzSW5OaGJIUWlPaUkzWnpkSE1EaHpOM0ZJV1NJc0ltTjBJam9pUmt4aGVIVm9NazlzY1RGVVQxRWlmUT09IiwiZW1haWwiOiJleUpwZGlJNklsbDBkbXRTSzFkcFJFb3lhR04xU1hGNmJVVTRWbEVpTENKMklqb3hMQ0pwZEdWeUlqb3hNREF3TENKcmN5STZNalUyTENKMGN5STZOalFzSW0xdlpHVWlPaUpqWTIwaUxDSmhaR0YwWVNJNklpSXNJbU5wY0dobGNpSTZJbUZsY3lJc0luTmhiSFFpT2lJM1p6ZEhNRGh6TjNGSVdTSXNJbU4wSWpvaU1HNDJOR3hEZVVabk1HOUhjMVI1YnpoTmN5SjkiLCJ1c2VybmFtZSI6ImV5SnBkaUk2SWtsSFdreGhOR1Z2T0hKTmRERnFObWRhT0U1QlZIY2lMQ0oySWpveExDSnBkR1Z5SWpveE1EQXdMQ0pyY3lJNk1qVTJMQ0owY3lJNk5qUXNJbTF2WkdVaU9pSmpZMjBpTENKaFpHRjBZU0k2SWlJc0ltTnBjR2hsY2lJNkltRmxjeUlzSW5OaGJIUWlPaUkzWnpkSE1EaHpOM0ZJV1NJc0ltTjBJam9pUmxKNVRFbFliMHRwYVdnek1VZ3liVVpaU1UwaWZRPT0iLCJwYXNzd29yZCI6ImV5SnBkaUk2SW14bFFqRlpVSFJxYWtReU5UWm9kMWw1WTNWdVVtY2lMQ0oySWpveExDSnBkR1Z5SWpveE1EQXdMQ0pyY3lJNk1qVTJMQ0owY3lJNk5qUXNJbTF2WkdVaU9pSmpZMjBpTENKaFpHRjBZU0k2SWlJc0ltTnBjR2hsY2lJNkltRmxjeUlzSW5OaGJIUWlPaUkzWnpkSE1EaHpOM0ZJV1NJc0ltTjBJam9pYWl0NFZFSjBVekIxTTBwT1FVd3JRM1ZUT0Rad1FVbHRUbW92T0RoUkluMD0iLCJ1cmwiOiJleUpwZGlJNkluSlFSV0U0Y1hKVGJHNTVkMUpuZUU1dlUxTlNlbmNpTENKMklqb3hMQ0pwZEdWeUlqb3hNREF3TENKcmN5STZNalUyTENKMGN5STZOalFzSW0xdlpHVWlPaUpqWTIwaUxDSmhaR0YwWVNJNklpSXNJbU5wY0dobGNpSTZJbUZsY3lJc0luTmhiSFFpT2lJM1p6ZEhNRGh6TjNGSVdTSXNJbU4wSWpvaVJ6VjFNRVZzVUcxMU0yOUVaeXRJVHlKOSIsImZhdmljb24iOm51bGwsInJlbmV3X2ludGVydmFsIjpudWxsLCJleHBpcmVfdGltZSI6MCwiZGVsZXRlX3RpbWUiOjAsImZpbGVzIjoiZXlKcGRpSTZJa3BTUWxaSE0wZENVblkwWjA1blEyZFNhR3h4UkhjaUxDSjJJam94TENKcGRHVnlJam94TURBd0xDSnJjeUk2TWpVMkxDSjBjeUk2TmpRc0ltMXZaR1VpT2lKalkyMGlMQ0poWkdGMFlTSTZJaUlzSW1OcGNHaGxjaUk2SW1GbGN5SXNJbk5oYkhRaU9pSTNaemRITURoek4zRklXU0lzSW1OMElqb2lSRkJwWVUxcmVTdFNRelpXYkVFaWZRPT0iLCJjdXN0b21fZmllbGRzIjoiZXlKcGRpSTZJazlwTmxSMGNtSlFabmhKU1ZwV0wwSnNhbUU1ZG1jaUxDSjJJam94TENKcGRHVnlJam94TURBd0xDSnJjeUk2TWpVMkxDSjBjeUk2TmpRc0ltMXZaR1VpT2lKalkyMGlMQ0poWkdGMFlTSTZJaUlzSW1OcGNHaGxjaUk2SW1GbGN5SXNJbk5oYkhRaU9pSTNaemRITURoek4zRklXU0lzSW1OMElqb2lkakYwUW1rM1YwRldSQzlrYTBFaWZRPT0iLCJvdHAiOiJleUpwZGlJNklrUmpSMkpITlZsQ2EzTXhRVGxpYkZGblpsTk9WSGNpTENKMklqb3hMQ0pwZEdWeUlqb3hNREF3TENKcmN5STZNalUyTENKMGN5STZOalFzSW0xdlpHVWlPaUpqWTIwaUxDSmhaR0YwWVNJNklpSXNJbU5wY0dobGNpSTZJbUZsY3lJc0luTmhiSFFpT2lJM1p6ZEhNRGh6TjNGSVdTSXNJbU4wSWpvaVpGaHpUMWxoYkVZNU1rRTNhVUVpZlE9PSIsImhpZGRlbiI6MCwic2hhcmVkX2tleSI6bnVsbH0',
		'edited_by'		=> 'sander',
	];

	/**
	 * @var CredentialRevision
	 */
	protected $revision;

	/**
	 * @after
	 */
	public function setUp() {
		$this->revision = CredentialRevision::fromRow(self::TEST_DATA);
	}

	/**
	 * @covers ::__construct
	 */
	public function testGetters() {
		$this->assertEquals(self::TEST_DATA['id'], $this->revision->getId());
		$this->assertEquals(self::TEST_DATA['guid'], $this->revision->getGuid());
		$this->assertEquals(self::TEST_DATA['credential_id'], $this->revision->getCredentialId());
		$this->assertEquals(self::TEST_DATA['user_id'], $this->revision->getUserId());
		$this->assertEquals(self::TEST_DATA['created'], $this->revision->getCreated());
		$this->assertEquals(self::TEST_DATA['credential_data'], $this->revision->getCredentialData());
		$this->assertEquals(self::TEST_DATA['edited_by'], $this->revision->getEditedBy());
	}

	/**
	 * @covers ::setter
	 * @depends testGetters
	 */
	public function testSetters() {
		/**
		 * Only testing one setter, if a custom setter is added a test should be made
		 */
		$this->revision->setEditedBy('WolFi');
		$this->assertEquals('WolFi', $this->revision->getEditedBy());
	}

	/**
	 * @covers ::jsonSerialize
	 */
	public function testJsonSerialize(){
		$expected_array = [
			'revision_id' => self::TEST_DATA['id'],
			'guid' => self::TEST_DATA['guid'],
			'created' => self::TEST_DATA['created'],
			'credential_data' => json_decode(base64_decode(self::TEST_DATA['credential_data'])),
			'edited_by' => self::TEST_DATA['edited_by'],
		];

		$actual_array = $this->revision->jsonSerialize();

		$this->assertEquals($expected_array, $actual_array);
	}
}