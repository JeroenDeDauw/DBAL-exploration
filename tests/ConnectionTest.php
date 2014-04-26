<?php

use Doctrine\DBAL\DriverManager;

class ConnectionTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $connection;

	public function setUp() {
		$config = new \Doctrine\DBAL\Configuration();

		$connectionParams = array(
			'driver' => 'pdo_sqlite',
			'memory' => true,
		);

		$this->connection = DriverManager::getConnection( $connectionParams, $config );
	}

	public function testConnection() {
		$this->assertTrue( $this->connection->connect() );
		$this->assertFalse( $this->connection->connect() );
	}

	public function testListTables() {
		$this->assertEquals(
			array(),
			$this->connection->getSchemaManager()->listTables()
		);
	}

}

