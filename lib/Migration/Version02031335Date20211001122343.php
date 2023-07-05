<?php

declare(strict_types=1);

namespace OCA\Passman\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * move favicon data to new icon column (for old installations)
 */
class Version02031335Date20211001122343 extends SimpleMigrationStep {

	protected $oldColumn = 'favicon';
	protected $newColumn = 'icon';
	protected $dataMigrationRequired = false;

	/**
	 * @param IDBConnection $connection
	 */
	public function __construct(
		protected IDBConnection $connection,
	) {
	}

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
			if ($table->hasColumn($this->oldColumn) && !$table->hasColumn($this->newColumn)) {
				$table->addColumn($this->newColumn, 'text', [
					'notnull' => false,
				]);
				$this->dataMigrationRequired = true;
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
		if ($this->dataMigrationRequired) {
			$updateQuery = $this->connection->getQueryBuilder();
			$updateQuery->update('passman_credentials')
				->set($this->newColumn, $this->oldColumn)
				->where($this->newColumn . ' IS NULL')
				->andWhere($this->oldColumn . ' IS NOT NULL')
				->executeStatement();

			/** @var ISchemaWrapper $schema */
			$schema = $schemaClosure();

			if ($schema->hasTable('passman_credentials')) {
				$table = $schema->getTable('passman_credentials');
				if ($table->hasColumn($this->oldColumn) && $table->hasColumn($this->newColumn)) {
					$dropColumnStatement = $this->connection->prepare('ALTER TABLE ' . $table->getName() . ' DROP COLUMN ' . $this->oldColumn . ';');
					$dropColumnStatement->execute();
				}
			}
		}
	}
}
