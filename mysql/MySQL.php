<?php

require 'MySQLResult.php';
require 'Collection.php';

class MySQL
{
	require 'Statement.php';

	/**
	 * Hostname of mysql database
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Username of Database
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * Password of Database
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Connection to database
	 *
	 * @var resource
	 */
	protected $connection;

	/**
	 * Last executed query
	 *
	 * @var string
	 */
	protected $query = '';

	/**
	 * Results from Database
	 *
	 * @var MySQLResult
	 */
	protected $results;

	/**
	 * Initialize class
	 *
	 * @param string $host
	 * @param string $username
	 * @param string $passord
	 * @param string $database
	 * @return void
	 */
	public function __construct($host, $username = NULL, $password = NULL, $database = NULL)
	{
		if (is_array($host))
		{
			$this->host = $host['host'];
			$this->username = $host['username'];
			$this->password = $host['password'];
			$this->database = $host['database'];
		}
		else
		{
			$this->host = $host;
			$this->username = $username;
			$this->password = $password;
			$this->database = $database;
		}

		$this->results = new Collection;

		$this->connection();
	}

	public static function connect($host, $username = NULL, $password = NULL, $database = NULL)
	{
		return new self($host, $username, $password, $database);
	}

	/**
	 * Connect to database
	 *
	 * @return void
	 */
	protected function connection()
	{
		$this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->database);

		if (!$this->connection)
			throw new Exeption('Could not connect to database');

		$this->selectdb($this->database);
	}

	/**
	 * Select database
	 *
	 * @param string $db
	 * @return this
	 */
	public function selectdb($db)
	{
		if(!mysqli_select_db($this->connection, $db))
			throw new Exception('Database does not exist');

		return $this;
	}

	/**
	 * Execute a MySQL Query
	 *
	 * @param string $query
	 * @return this
	 */
	public function query($query = NULL)
	{
		$this->query = $query;
		$this->results = [];

		$results = mysqli_query($this->connection, $query);

		if (is_bool($results))
		{
			$this->results = new Collection;
			return $this;
		}

		while($row = mysqli_fetch_array($results))
		{
			$result = new MySQLResult($row);
			$this->results->add($result);
		}

		return $this;
	}

	/**
	 * Alias for get method
	 *
	 * @return Collection|bool
	 */
	public function results()
	{
		return $this->get();
	}

	/**
	 * Get the last query executed
	 *
	 * @return string
	 */
	public function lastQuery()
	{
		return $this->query;
	}

	/**
	 * Get first result from database
	 *
	 * @return object
	 */
	public function first()
	{
		return !empty($this->results) ? is_bool($this->results) ? $this->results : $this->results->first() : NULL;
	}

	/**
	 * ToString Implementation
	 *
	 * @return string
	 */
	public function __tostring()
	{
		return $this->query;
	}
}