<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\Database;


class Database {

	private $server;
	private $host;
	private $port;
	private $user;
	private $pass;
	private $dbName;

	private $db;


	public function __construct($configs = array())
	{
		if(isset($configs)) {
			$this->server = $configs['server'];
			$this->host = $configs['host'];
			$this->port = $configs['port'];
			$this->user = $configs['user'];
			$this->pass = $configs['pass'];
			$this->dbName = $configs['dbname'];
		}

		try {
			$this->db = new \PDO(
				"$this->server:host=$this->host:$this->port;dbname=$this->dbName",
				$this->user,
				$this->pass,
				array(
					\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
				)
			);
		} catch (\PDOException $e) {
			throw new \Exception("Impossible de se connecter, cause ".$e->getMessage());
		}
	}


	public function query($sql, $params = array(), $out = PDO::FETCH_OBJ) {
		$data = array();

		try {
			$req = $this->db->prepare($sql);
			$req->execute($params);
			$data = $req->fetchAll($out);
		} catch(\PDOException $e) {
			throw new \Exception($e->getMessage());
		}

		return $data;
		
	}


	public function exec($sql, $params = array()) {
		try {
			$req = $this->db->prepare($sql);

			$position = 1;

			foreach($params as $p) {
				$req->bindValue($position, $p);
				$position++;
			}

			return $req->execute();
		} catch(\PDOException $e) {
			throw new \Exception($e->getMessage());
		}
	}

}

?>