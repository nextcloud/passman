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
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Server;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
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
}
