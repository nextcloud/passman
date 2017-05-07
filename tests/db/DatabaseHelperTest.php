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

use \OCA\Passman\AppInfo\Application;

abstract class DatabaseHelperTest extends PHPUnit_Extensions_Database_TestCase {
	CONST DUMPS_DIR = __DIR__ . '/dumps/';

	/**
	 * This function should return the table name, for example
	 * for a test running on oc_passman_vaults it shall return ["oc_passman_vaults"]
	 * @internal
	 * @return string[]
	 */
	abstract public function getTablesNames();

	/**
	 * @var Application
	 */
	protected $app_container;

	/**
	 * @var \OCP\IDBConnection
	 */
	protected $db;

	/**
	 * @var PHPUnit_Extensions_Database_DataSet_MysqlXmlDataSet[]
	 */
	protected $datasets;
	
	/**
	 *
	 * @var \OC\Server
	 */
	static protected $server = NULL;

	public function __construct() {
		parent::__construct();
	}

	public function setUp() {
		if (self::$server === NULL) self::$server = new \OC\Server(getenv('SERVER_BASE_DIR'), new \OC\Config(getenv('SERVER_CONFIG_DIR'), getenv('SERVER_CONFIG_FILE')));

		$this->db = self::$server->getDatabaseConnection();

		$this->datasets = [];
		$tables = $this->getTablesNames();
		foreach ($tables as $table){
			$this->datasets[$table] = $this->createMySQLXMLDataSet(self::DUMPS_DIR . $table . '.xml');
			$this->setUpTable($table);
		}
	}

	/**
	 * Truncates a table, platform aware
	 * @param $table_name
	 */
	public function truncateTable($table_name) {
		$this->db->executeQuery($this->db->getDatabasePlatform()->getTruncateTableSQL($table_name));
	}

	/**
	 * Initializes the table with the corresponding dataset on the dumps dir
	 *
	 * @param $table_name
	 */
	public function setUpTable($table_name){
		$table = $this->getTableDataset($table_name);
		$table_no_prefix = substr($table_name, 3);

		// Cleanup any data currently inside the table
		$this->truncateTable($table_name);

		// Fill the table with the data dumps
		for ($i = 0; $i < $table->getRowCount(); $i++) {
			$row = $table->getRow($i);

			$qb = $this->db->getQueryBuilder();
			$qb->insert($table_no_prefix);

			foreach ($row as $key => $value){
				if (is_null($value)) {
					$value = 'NULL';
				}
				else if (!is_numeric($value)) {
					$value = "'{$value}'";
				}

				$qb->setValue($key,  $value);
			}

			$qb->execute();
			$this->db->lastInsertId();
		}
	}

	/**
	 * The database dumps must be generated with this command:
	 * 	mysqldump --xml -t -u db_username -p database table > table_name.xml
	 * @param $table_name
	 * @return PHPUnit_Extensions_Database_DataSet_ITable
	 */
	public function getTableDataset($table_name) {
		return $this->datasets[$table_name]->getTable($table_name);
	}

	/**
	 * Finds a subset of rows from the dataset which field name matches
	 * the specified value
	 * @param $table_name	The name of the table to search into
	 * @param $field_name	The field name
	 * @param $value_match	The value to match data against
	 * @return array		An array of rows
	 */
	public function findInDataset($table_name, $field_name, $value_match) {
		$table = $this->getTableDataset($table_name);
		$rows = $table->getRowCount();

		$result = [];
		for ($i = 0; $i < $rows; $i++) {
			$row = $table->getRow($i);
			if ($row[$field_name] == $value_match){
				$result[] = $row;
			}
		}

		return $result;
	}

	/**
	 * Filters the given array
	 * @param $dataset		The data to filter
	 * @param $field_name	The array key to match against
	 * @param $value_match	The value to compare to
	 */
	public function filterDataset($dataset, $field_name, $value_match) {
		$ret = [];

		foreach ($dataset as $value){
			if ($value[$field_name] == $value_match){
				$ret[] = $value;
			}
		}

		return $ret;
	}

	/**
	 * @coversNothing
	 */
	public function testTablesSetup() {
		$tables = $this->getTablesNames();
		foreach ($tables as $table) {
			$table = substr($table, 3);
			$this->assertTrue($this->db->tableExists($table));
		}
	}

	/**
	 * DO NOT USE
	 *
	 * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	protected function getConnection() {
		return $this->getMockForAbstractClass(PHPUnit_Extensions_Database_DB_IDatabaseConnection::class);
	}

	/**
	 * DO NOT USE
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet() {
		foreach ($this->datasets as $value) return $value;
	}
}
