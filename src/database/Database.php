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

	private \PDO $db;


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
				"$this->server:host=$this->host;dbname=$this->dbName",
				$this->user,
				$this->pass,
				array(
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
				)
			);

			$this->db->exec("SET NAMES UTF8");
		} catch (\PDOException $e) {
			throw new \Exception("Unable to connect, cause ".$e->getMessage());
		}
	}


	public function query($sql, $params = array(), $out = \PDO::FETCH_OBJ) {
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


	public function exec(string $sql, $params = array()) {
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


	public function insert($sql, $params = array()) {
		$typeReq = explode(' ', $sql);

		if($typeReq[0] != strtoupper('insert'))
			throw new \Exception('The query string specified as parameter is incompatible with this function');

		$req = $this->exec($sql, $params);

		if($req)
			return $this->db->lastInsertId();
		else
			return false;
		
	}


	public function getDB() { return $this->db; }

}

?>
