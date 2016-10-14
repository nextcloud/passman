<?php
/**
 *
 * Date: 10/10/16
 * Time: 12:47
 *
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license AGPLv3
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

	public function __construct() {
		parent::__construct();
	}

	public function setUp() {
		$server = new \OC\Server(getenv('SERVER_BASE_DIR'), new \OC\Config(getenv('SERVER_CONFIG_DIR'), getenv('SERVER_CONFIG_FILE')));
		$this->app_container = $server->getAppContainer('passman');

		$this->db = $this->app_container->getServer()->getDatabaseConnection();

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

		// Cleanup any data currently inside the table
		$this->truncateTable($table_name);

		// Fill the table with the data dumps
		for ($i = 0; $i < $table->getRowCount(); $i++) {
			$row = $table->getRow($i);

			$fields = "";
			$values = "";
			foreach ($row as $key => $value){
				$fields .= "`{$key}`, ";
				$values .= is_numeric($value) ? $value : ("'{$value}'") ;
				$values .= ", ";
			}

			$fields = substr($fields, 0, count($fields) -3);
			$values = substr($values, 0, count($values) -3);

			$q = "INSERT INTO $table_name ({$fields}) VALUES ({$values});";
			$this->db->executeQuery($q);
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
			if ($row[$field_name] === $value_match){
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
			if ($value[$field_name] === $value_match){
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
