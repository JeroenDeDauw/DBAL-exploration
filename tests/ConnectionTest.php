<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;

class ConnectionTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $connection;

	public function setUp() {
		$config = new \Doctrine\DBAL\Configuration();

		$connectionParams = [
			'driver' => 'pdo_sqlite',
			'memory' => true,
		];

		$this->connection = DriverManager::getConnection( $connectionParams, $config );
	}

	public function testConnection() {
		$this->assertTrue( $this->connection->connect() );
		$this->assertFalse( $this->connection->connect() );
	}

	public function testListTables() {
		$this->assertEquals(
			[],
			$this->connection->getSchemaManager()->listTables()
		);
	}

	public function testCreateTable() {
		$schema = new \Doctrine\DBAL\Schema\Schema();

		$table = $schema->createTable( 'users' );
		$table->addColumn( 'id', 'integer', [ 'unsigned' => true ] );
		$table->addColumn( 'username', 'string', [ 'length' => 32 ] );
		$table->setPrimaryKey( [ 'id' ] );
		$table->addUniqueIndex( [ 'username' ] );

		$this->persistSchema( $schema );
		
		$this->assertEquals(
			[
				'id',
				'username'
			],
			array_keys( $this->connection->getSchemaManager()->listTableColumns( 'users' ) )
		);

		$currentSchema = $this->connection->getSchemaManager()->createSchema();
		$schema->dropTable( 'users' );

		$comparator = new \Doctrine\DBAL\Schema\Comparator();
		$schemaDiff = $comparator->compare($currentSchema, $schema);

		foreach ( $schemaDiff->toSql( $this->connection->getDatabasePlatform() ) as $query ) {
			$this->connection->exec( $query );
		}

		$this->assertEquals( [], $this->connection->getSchemaManager()->listTables() );
	}

	private function persistSchema( Schema $schema ) {
		$queries = $schema->toSql( $this->connection->getDatabasePlatform() );

		foreach ( $queries as $query ) {
			$this->connection->exec( $query );
		}
	}

	public function testGetNonExistingTable() {
		$this->assertInstanceOf(
			'Doctrine\DBAL\Schema\Table',
			$this->connection->getSchemaManager()->listTableDetails( 'kittens' )
		);

		$this->assertFalse( $this->connection->getSchemaManager()->tablesExist( 'kittens' ) );
	}

}

