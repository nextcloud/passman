<?php

declare(strict_types=1);

namespace OCA\Passman\Migration;

use Closure;
use Doctrine\DBAL\Types\Type;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * fix failed label column index generation
 */
class Version02031334Date20210926234011 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('passman_credentials')) {
			$table = $schema->getTable('passman_credentials');
			if ($table->hasIndex('passman_credential_label_index')) {
				$table->dropIndex('passman_credential_label_index');
			}
			$labelColumn = $table->getColumn('label');
			$typeName = $labelColumn->getType()->getName();
			if (($labelColumn->getLength() ?? 0) < 2048 || $typeName !== Types::STRING) {
				if (interface_exists(\OCP\DB\Schema\ITable::class) && $table instanceof \OCP\DB\Schema\ITable) {
					$table->modifyColumn('label', [
						'type' => Types::STRING,
						'length' => 2048,
					]);
				} else {
					// Doctrine Table::modifyColumn() TypeErrors on a string type name.
					$table->modifyColumn('label', [
						'type' => Type::getType(Types::STRING),
						'length' => 2048,
					]);
				}
			}
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
