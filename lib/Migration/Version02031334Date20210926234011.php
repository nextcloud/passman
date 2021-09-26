<?php

declare(strict_types=1);

namespace OCA\Passman\Migration;

use Closure;
use Doctrine\DBAL\Types\Type;
use OCP\DB\ISchemaWrapper;
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
			if ($labelColumn->getLength() < 2048 || $labelColumn->getType() !== Type::getType('string')) {
				$table->changeColumn('label', [
					'type' => Type::getType('string'),
					'length' => 2048
				]);
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
