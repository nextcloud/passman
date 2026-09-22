<?php
/**
 * Nextcloud - Passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
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

namespace OCA\Passman\Tests\Unit\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaConfig;
use Doctrine\DBAL\Types\Type;
use OC\DB\Connection;
use OC\DB\SchemaWrapper;
use OCA\Passman\Migration\Version02031334Date20210926234011;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Server;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionProperty;
use Test\TestCase;
use TypeError;

/**
 * Contract for Version02031334: migrating credentials.label from TEXT to STRING(2048)
 * using APIs that exist on both Nextcloud 34 (Doctrine Table) and 35 (OCP ITable).
 *
 * The migration before our NC35 changes compared getType() to Type::getType('string')
 * and calls changeColumn(). That identity check is not portable, and changeColumn() is not
 * declared on NC 35's ITable (only forwarded via __call).
 */
#[Group(name: 'DB')]
#[CoversNothing]
class Version02031334Date20210926234011Test extends TestCase {
	private Connection $connection;
	private string     $tableName;

	protected function setUp(): void {
		parent::setUp();

		$this->connection = Server::get(Connection::class);
		$this->tableName = strtolower(self::getUniqueID('pmanl_'));
	}

	protected function tearDown(): void {
		$this->connection->dropTable($this->tableName);
		parent::tearDown();
	}

	public function testDoctrineTypeIdentityCannotDetectStringColumnsOnNc35(): void {
		$this->persistLabelTable(Types::STRING, 2048);
		$column = $this->reloadTable()->getColumn('label');
		$type = $column->getType();

		$this->assertSame(Types::STRING, $type->getName());
		$this->assertSame(2048, $column->getLength());

		$currentWouldRewrite = $column->getLength() < 2048 || $type !== Type::getType(Types::STRING);
		$plannedWouldRewrite = ($column->getLength() ?? 0) < 2048 || $type->getName() !== Types::STRING;

		$this->assertFalse($plannedWouldRewrite, 'getType()->getName() !== Types::STRING must be a no-op for an already-string(2048) column');
		$this->assertSame(
			!$type instanceof Type,
			$currentWouldRewrite,
			'Type::getType(\'string\') identity is only valid while getType() still returns a Doctrine Type',
		);
	}

	public function testChangeColumnIsNotDeclaredOnTheNc35TableApi(): void {
		$this->persistLabelTable(Types::TEXT);
		$table = $this->reloadTable();
		$methods = new ReflectionClass($table);

		$this->assertTrue($methods->hasMethod('modifyColumn'));
		if ($this->tableUsesOcpSchemaApi($table)) {
			$this->assertFalse($methods->hasMethod('changeColumn'));
			return;
		}

		$this->assertTrue($methods->hasMethod('changeColumn'));
	}

	public function testTypesStringModifyOptionRequiresTheITableWrapper(): void {
		$this->persistLabelTable(Types::TEXT);
		$schema = new SchemaWrapper($this->connection);
		$table = $schema->getTable($this->tableName);

		if ($this->tableUsesOcpSchemaApi($table)) {
			$table->modifyColumn('label', [
				'type'   => Types::STRING,
				'length' => 2048,
			]);
			$this->assertSame(Types::STRING, $table->getColumn('label')->getType()->getName());
			$this->assertSame(2048, $table->getColumn('label')->getLength());
			return;
		}

		$this->expectException(TypeError::class);
		$table->modifyColumn('label', [
			'type'   => Types::STRING,
			'length' => 2048,
		]);
	}

	// test against the real migration code
	public function testChangeSchemaWidensTextLabelToString2048(): void {
		$schema = $this->isolatedPassmanCredentialsSchema(Types::TEXT);
		$migration = new Version02031334Date20210926234011();

		$result = $migration->changeSchema(
			$this->createStub(IOutput::class),
			static fn () => $schema,
			[],
		);

		$this->assertNotNull($result);
		$column = $result->getTable('passman_credentials')->getColumn('label');
		$this->assertSame(Types::STRING, $column->getType()->getName());
		$this->assertSame(2048, $column->getLength());
	}

	// test against the real migration code
	public function testChangeSchemaLeavesString2048Unchanged(): void {
		$schema = $this->isolatedPassmanCredentialsSchema(Types::STRING, 2048);
		$migration = new Version02031334Date20210926234011();

		$result = $migration->changeSchema(
			$this->createStub(IOutput::class),
			static fn () => $schema,
			[],
		);

		$this->assertNotNull($result);
		$column = $result->getTable('passman_credentials')->getColumn('label');
		$this->assertSame(Types::STRING, $column->getType()->getName());
		$this->assertSame(2048, $column->getLength());
	}

	public function testDualCompatWidenPersistsTextLabelAsString2048(): void {
		$this->persistLabelTable(Types::TEXT);
		$before = $this->reloadTable()->getColumn('label');
		$this->assertSame(Types::TEXT, $before->getType()->getName());

		$schema = new SchemaWrapper($this->connection);
		$this->widenLabelToString2048($schema->getTable($this->tableName));
		$this->connection->migrateToSchema($schema->getWrappedSchema());

		$after = $this->reloadTable()->getColumn('label');
		$this->assertSame(Types::STRING, $after->getType()->getName());
		$this->assertSame(2048, $after->getLength());
	}

	/**
	 * Runs the migration class against a persisted credentials-shaped table:
	 * label TEXT NOT NULL, the other credential TEXT columns, and passman_credential_label_index.
	 * A normal row and a 2048-character row must still be readable after migrateToSchema().
	 *
	 * changeSchema() always addresses passman_credentials.
	 * The connection prefix is pointed at a throwaway table for the apply, then restored.
	 */
	public function testChangeSchemaPersistsTextLabelAsString2048AndKeepsRows(): void {
		$installedLabel = $this->labelColumnSnapshot('passman_credentials');
		$normalLabel = 'mailbox';
		$boundaryLabel = str_repeat('x', 2048);
		$rows = [
			[
				'vault_id'    => 1,
				'label'       => $normalLabel,
				'description' => 'personal mailbox',
				'username'    => 'ada',
				'password'    => 'secret',
				'url'         => 'https://example.com',
			],
			[
				'vault_id'    => 2,
				'label'       => $boundaryLabel,
				'description' => 'boundary',
				'username'    => 'wolfi',
				'password'    => 'secret-2',
				'url'         => 'https://example.org',
			],
		];

		$this->withThrowawayTablePrefix(function () use ($rows): void {
			$this->persistCredentialsShapedTable();
			foreach ($rows as $row) {
				$this->insertCredentialRow($row);
			}

			$before = $this->reloadCredentialsTable();
			$this->assertSame(Types::TEXT, $before->getColumn('label')->getType()->getName());
			$this->assertTrue($before->getColumn('label')->getNotnull());
			$this->assertTrue($before->hasIndex('passman_credential_label_index'));

			$schema = new SchemaWrapper($this->connection);
			$migration = new Version02031334Date20210926234011();
			$result = $migration->changeSchema(
				$this->createStub(IOutput::class),
				static fn () => $schema,
				[],
			);

			$this->assertNotNull($result);
			$changed = $result->getTable('passman_credentials')->getColumn('label');
			$this->assertSame(Types::STRING, $changed->getType()->getName());
			$this->assertSame(2048, $changed->getLength());
			$this->assertTrue($changed->getNotnull());
			$this->assertFalse($result->getTable('passman_credentials')->hasIndex('passman_credential_label_index'));

			$this->connection->migrateToSchema($result->getWrappedSchema());

			$after = $this->reloadCredentialsTable();
			$column = $after->getColumn('label');
			$this->assertSame(Types::STRING, $column->getType()->getName());
			$this->assertSame(2048, $column->getLength());
			$this->assertTrue($column->getNotnull());
			$this->assertFalse($after->hasIndex('passman_credential_label_index'));

			$stored = $this->fetchCredentialRows();
			$this->assertCount(count($rows), $stored);
			foreach ($rows as $index => $expected) {
				$this->assertSame($expected['label'], $stored[$index]['label']);
				$this->assertSame($expected['description'], $stored[$index]['description']);
				$this->assertSame($expected['username'], $stored[$index]['username']);
				$this->assertSame($expected['password'], $stored[$index]['password']);
				$this->assertSame($expected['url'], $stored[$index]['url']);
				$this->assertSame((string)$expected['vault_id'], (string)$stored[$index]['vault_id']);
				$this->assertNull($stored[$index]['email']);
				$this->assertNull($stored[$index]['icon']);
			}
		});

		$this->assertLabelColumnUnchanged('passman_credentials', $installedLabel);
	}

	private function isolatedPassmanCredentialsSchema(string $labelType, ?int $length = null): SchemaWrapper {
		$config = new SchemaConfig();
		$config->setName($this->connection->getDatabase());
		$doctrineSchema = new Schema([], [], $config);
		$table = $doctrineSchema->createTable($this->connection->getPrefix() . 'passman_credentials');
		$options = ['notnull' => true];
		if ($length !== null) {
			$options['length'] = $length;
		}
		$table->addColumn('label', $labelType, $options);

		return new SchemaWrapper($this->connection, $doctrineSchema);
	}

	private function persistLabelTable(string $type, ?int $length = null): void {
		$schema = new SchemaWrapper($this->connection);
		$table = $schema->createTable($this->tableName);
		$table->addColumn('id', Types::INTEGER, [
			'notnull'       => true,
			'autoincrement' => true,
		]);
		$options = ['notnull' => true];
		if ($length !== null) {
			$options['length'] = $length;
		}
		$table->addColumn('label', $type, $options);
		$table->setPrimaryKey(['id']);
		$this->connection->migrateToSchema($schema->getWrappedSchema());
	}

	private function reloadTable(): object {
		return (new SchemaWrapper($this->connection))->getTable($this->tableName);
	}

	/**
	 * NC 35 ITable::modifyColumn() converts Types::* strings to DBAL types.
	 * NC 34 Doctrine Table::modifyColumn() requires a Type instance.
	 */
	private function widenLabelToString2048(object $table): void {
		$type = Types::STRING;
		if (!$this->tableUsesOcpSchemaApi($table)) {
			$type = Type::getType(Types::STRING);
		}

		$table->modifyColumn('label', [
			'type'   => $type,
			'length' => 2048,
		]);
	}

	private function tableUsesOcpSchemaApi(object $table): bool {
		return interface_exists(\OCP\DB\Schema\ITable::class)
			&& $table instanceof \OCP\DB\Schema\ITable;
	}

	/**
	 * @param callable(): void $callback
	 */
	private function withThrowawayTablePrefix(callable $callback): void {
		$original = $this->connection->getPrefix();
		$this->setTablePrefix($original . $this->tableName . '_');
		try {
			$callback();
		} finally {
			try {
				if ($this->connection->tableExists('passman_credentials')) {
					$this->connection->dropTable('passman_credentials');
				}
			} finally {
				$this->setTablePrefix($original);
			}
		}
	}

	private function setTablePrefix(string $prefix): void {
		$property = new ReflectionProperty(Connection::class, 'tablePrefix');
		$property->setValue($this->connection, $prefix);
	}

	/**
	 * Text columns from Version020308's passman_credentials table, besides label.
	 *
	 * @return list<string>
	 */
	private function credentialTextColumns(): array {
		return [
			'description',
			'tags',
			'email',
			'username',
			'password',
			'url',
			'files',
			'custom_fields',
			'otp',
			'compromised',
			'shared_key',
			'icon',
		];
	}

	private function persistCredentialsShapedTable(): void {
		$schema = new SchemaWrapper($this->connection);
		$table = $schema->createTable('passman_credentials');
		$table->addColumn('id', Types::INTEGER, [
			'notnull'       => true,
			'autoincrement' => true,
		]);
		$table->addColumn('vault_id', Types::BIGINT, [
			'notnull' => true,
		]);
		$table->addColumn('label', Types::TEXT, [
			'notnull' => true,
		]);
		foreach ($this->credentialTextColumns() as $column) {
			$table->addColumn($column, Types::TEXT, [
				'notnull' => false,
			]);
		}
		$table->setPrimaryKey(['id']);
		$table->addIndex(['label'], 'passman_credential_label_index', [], $this->labelIndexOptions());
		$this->connection->migrateToSchema($schema->getWrappedSchema());
	}

	/**
	 * MySQL refuses a TEXT index without a prefix length.
	 * The migration dropspassman_credential_label_index before it widens label.
	 *
	 * @return array<string, mixed>
	 */
	private function labelIndexOptions(): array {
		$platform = $this->connection->getDatabasePlatform()::class;
		$needsPrefixLength = str_contains($platform, 'MySQL')
			|| str_contains($platform, 'MySql')
			|| str_contains($platform, 'MariaDB');
		if (!$needsPrefixLength) {
			return [];
		}

		return ['lengths' => [64]];
	}

	/**
	 * @param array{vault_id: int, label: string, description: string, username: string, password: string, url: string} $row
	 */
	private function insertCredentialRow(array $row): void {
		$qb = $this->connection->getQueryBuilder();
		$qb->insert('passman_credentials')
			->values([
				'vault_id'    => $qb->createNamedParameter($row['vault_id'], IQueryBuilder::PARAM_INT),
				'label'       => $qb->createNamedParameter($row['label']),
				'description' => $qb->createNamedParameter($row['description']),
				'username'    => $qb->createNamedParameter($row['username']),
				'password'    => $qb->createNamedParameter($row['password']),
				'url'         => $qb->createNamedParameter($row['url']),
			])
			->executeStatement();
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	private function fetchCredentialRows(): array {
		$qb = $this->connection->getQueryBuilder();

		return $qb->select('vault_id', 'label', 'description', 'username', 'password', 'url', 'email', 'icon')
			->from('passman_credentials')
			->orderBy('id', 'ASC')
			->executeQuery()
			->fetchAllAssociative();
	}

	private function reloadCredentialsTable(): object {
		return (new SchemaWrapper($this->connection))->getTable('passman_credentials');
	}

	/**
	 * @return array{type: string, length: ?int, notnull: bool}|null
	 */
	private function labelColumnSnapshot(string $tableName): ?array {
		if (!$this->connection->tableExists($tableName)) {
			return null;
		}

		$column = (new SchemaWrapper($this->connection))->getTable($tableName)->getColumn('label');

		return [
			'type'    => $column->getType()->getName(),
			'length'  => $column->getLength(),
			'notnull' => $column->getNotnull(),
		];
	}

	/**
	 * @param array{type: string, length: ?int, notnull: bool}|null $snapshot
	 */
	private function assertLabelColumnUnchanged(string $tableName, ?array $snapshot): void {
		if ($snapshot === null) {
			$this->assertFalse($this->connection->tableExists($tableName));
			return;
		}

		$this->assertSame($snapshot, $this->labelColumnSnapshot($tableName));
	}
}
