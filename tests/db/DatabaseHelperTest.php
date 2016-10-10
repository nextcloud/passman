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
		$server = new \OC\Server(getenv('NEXTCLOUD_BASE_DIR'), new \OC\Config(getenv('NEXTCLOUD_CONFIG_DIR'), getenv('NEXTCLOUD_CONFIG_FILE')));
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
	 * Initializes the table with the corresponding dataset on the dumps dir
	 *
	 * @param $table_name
	 */
	public function setUpTable($table_name){
		$table = $this->getTableDataset($table_name);

		// Cleanup any data currently inside the table
		$this->db->executeQuery("TRUNCATE $table_name;");

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
