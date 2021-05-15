<?php

	namespace DataSaver;

	use \DataSaver\Conn;

	class DB {

		public function __construct($dbname) {
			$this->dbname = $dbname;
			$this->conn = Conn::get();
		}

		public function getDBName() {
			return $this->dbname;
		}

		public function create() {
			$query = $this->conn->query("create database if not exists " . $this->getDBName());
			$this->conn->close();
			return (bool)$query;
		}

	}

?>
