<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\App\Repository;


use Gravity\Core\App\Entity\Entity;
use Gravity\Core\Database\Database;
use Gravity\Core\Exceptions\BadRequestException;
use Gravity\Core\Exceptions\ControllerException;



abstract class Repository {

    protected static $entity;
	protected static $db;
    protected static $table;
    protected static $columns = array();

	protected static $primary_key = "";


	protected static function getDatabase($configs = array()) {
		static::$primary_key = (static::$primary_key == "")?"id":static::$primary_key;
		return static::$db = new Database(require("../configs/database.php"));
	}



	
	/**
	 * Trouver tous les enregistrements
	 * @param array|null $columns Colonnes à récupérer
	 * @param string|null $orderClause Colonnes à ordonner
	 * @param string|null $orderArgs type d'ordonancement (ASC ou DESC)
	 * @param int|null $limit Nombre d'enregistrements à récupérer
	 * @param int|null $offset point de départ
	 * @return array
	*/
	public static function findAll(array $columns = null, string $orderClause = null, string $orderArgs = null, int $limit = null, int $offset = null) {

		static::validEntity();
		
		$entities = array();

		$columns = (!\is_null($columns)) ? $columns : ['*'];

		$columnString = (\count($columns) > 1) ? \implode(',', $columns) : $columns[0];

		$orderString = (!\is_null($orderClause) && !\is_null($orderArgs)) ? "order by $orderClause $orderArgs" : "";

		$limitString = (!\is_null($limit)) ? "limit ".$limit : "";

		$offsetString = (!\is_null($offset)) ? "offset ".$offset : "";

		$req = self::getDatabase()->query("select $columnString from ". static::$table ." $orderString $limitString $offsetString", array(), \PDO::FETCH_ASSOC);

		foreach($req as $e) {
			if(!array_key_exists(static::$primary_key, $e))
				throw new ControllerException("Unable to find primary key ".static::$primary_key." on ".static::class);

			array_push($entities, (new static::$entity($e))->setId($e[static::$primary_key]));
		}

		return $entities;

	}


	/**
	 * Trouver un enregistrement à partir d'un identifiant unique (l'id)
	 * @param mixed $id
	 * @return object|null
	*/
	public static function find($id) {

		static::validEntity();

		$req = self::getDatabase()->query("select * from ". static::$table ." where ".static::$primary_key." = ?", array($id), \PDO::FETCH_ASSOC);

		if(count($req) < 1)
			return null;

		return (new static::$entity($req[0]))->setId($req[0][static::$primary_key]);
		
	}

	
 	/**
  	 * Trouver un/des enregistrement(s) à partir d'une/des condition(s)
  	 * @param array $whereColums colonnes sur lesquelles les conditions ont été posées
  	 * @param array $values valeurs colonnes conditionnées
	 * @param string|null $orderClause Colonnes à ordonner
	 * @param string|null $orderArgs type d'ordonancement (ASC ou DESC)
	 * @param int|null $limit Nombre d'enregistrements à récupérer
	 * @param int|null $offset point de départ
  	 * @return array|object
	 * 
	 * @throws ControllerException
  	*/
	public static function findWhere(array $whereColums, array $values, string $orderClause = null, string $orderArgs = null, int $limit = null, int $offset = null) {

		static::validEntity();

		self::getDatabase();

		if(\count($whereColums) < 1)
			throw new ControllerException("Column(s) not specified on ".static::class."::findWhere method");

		if(\count($values) < 1)
			throw new ControllerException("Column(s) value(s) not specified on ".static::class."::findWhere method");

		$whereClause = (\count($whereColums) < 1)?static::$primary_key."=?":\implode('=? and ', $whereColums);

		$whereClause .= (\count($whereColums) > 0)?'=?':'';

		$orderString = (!\is_null($orderClause) && !\is_null($orderArgs)) ? "order by $orderClause $orderArgs" : "";

		$limitString = (!\is_null($limit)) ? "limit ".$limit : "";

		$offsetString = (!\is_null($offset)) ? "offset ".$offset : "";

		$req = self::getDatabase()->query("select * from ". static::$table ." where ". $whereClause . " $orderString $limitString $offsetString", $values, \PDO::FETCH_ASSOC);

		if(\count($req) > 1) {

			$entities = array();

			foreach($req as $e) {
				array_push($entities, (new static::$entity($e))->setId($e[static::$primary_key]));
			}

			return $entities;
		} else if(\count($req) == 1) {
			return (new static::$entity($req[0]))->setId($req[0][static::$primary_key]);
		} else {
			return array();
		}
		
	}


	/**
	 * Effectuer votre propre requete personnalisée pour obtenir une/des entité(s)
	 * @param string $req votre requete personnalisée
	 * @param array $values vos données si vous utilisez une requete préparée
	 * @param string|null $orderClause Colonnes à ordonner
	 * @param string|null $orderArgs type d'ordonancement (ASC ou DESC)
	 * @param int|null $limit Nombre d'enregistrements à récupérer
	 * @param int|null $offset point de départ
	 * @return array|object|null
	*/
	public static function fromQuery(string $req, array $values = array(), string $orderClause = null, string $orderArgs = null, int $limit = null, int $offset = null) {

		static::validEntity();

		static::validTable($req);

		$orderString = (!\is_null($orderClause) && !\is_null($orderArgs)) ? "order by $orderClause $orderArgs" : "";

		$limitString = (!\is_null($limit)) ? "limit ".$limit : "";

		$offsetString = (!\is_null($offset)) ? "offset ".$offset : "";

		$req = self::getDatabase()->query($req." $orderString $limitString $offsetString", $values, \PDO::FETCH_ASSOC);

		if(\count($req) > 1) {

			$entities = array();

			foreach($req as $e) {
				array_push($entities, (new static::$entity($e))->setId($e[static::$primary_key]));
			}

			return $entities;
		} else if(\count($req) == 1) {
			return (new static::$entity($req[0]))->setId($req[0][static::$primary_key]);
		} else {
			return array();
		}
		
	}


	/**
	 * Effectuer un enregistrement
	 * @param Entity $entity l'entité à persister dans la base
	 * @return bool|null
	 * 
	 * @throws BadRequestException
	*/
	public static function save(Entity $entity) {

		static::validEntity();

		try {

			self::getDatabase();

			//On vérifie que tous les champs obligatoires sont renseignés
			if(\count((new static::$entity())->getRequiredColumns()) > 0) {

				foreach((new static::$entity())->getRequiredColumns() as $r) {
					if(!array_key_exists($r, $entity->toArray()))
						throw new BadRequestException("{$r} field required");
				}
			}

			$id = (array_key_exists(static::$primary_key, $entity->toArray()))
				?$entity->toArray()[static::$primary_key]
				:$entity->toArray()['id'];

			$entity = $entity->setId($id);			

			$dataArray = $entity->toArray();

			unset($dataArray['id']);

			$dataArray[static::$primary_key] = $id;

			$placeHoldersArr = array();

			foreach($dataArray as $elem) {
				$placeHoldersArr[] = "?";
			}

			$placeHolders = \implode(",", $placeHoldersArr);


			$sql = "insert into ". static::$table ." (". \implode(',', array_keys($dataArray)) .") values(". $placeHolders .")";
			

			$req = self::getDatabase()->exec($sql, $dataArray);

			return $req;
		} catch(BadRequestException $e) {
			throw new BadRequestException($e->getMessage());
		} catch(\Exception $e) {
			throw new \Exception($e);
		}
		
	}


	/**
	 * Mise à jour d'un enregistrement
	 * @param array $data les nouvelles données à persister dans la base
	 * @param mixed $where l'id correspondant
	 * @return bool|null
	 * 
	 * @throws BadRequestException
	*/
	public static function update(array $data, $where) {

		static::validEntity();

		self::getDatabase();

		$response = false;

		//On vérifie que tous les champs obligatoires sont renseignés
		if(\count((new static::$entity())->getRequiredColumns()) > 0) {

			foreach((new static::$entity())->getRequiredColumns() as $r) {

				if($r == static::$primary_key) //Inutil pour la clé primaire puisqu'elle est déjà en argument de la fonction
					continue;

				if(!array_key_exists($r, $data))
					throw new BadRequestException("{$r} field required");
			}
		}

		$columns = array();

		foreach($data as $k => $v) {
			$columns[] = $k."=?";
		}

		$columnsString = \implode(',', $columns);

		$sqlDel = "update ". static::$table . " set ". $columnsString ." where " . static::$primary_key ." = ?";

		$data['id'] = $where;

		$req = self::getDatabase()->exec($sqlDel, $data);

		if($req) {
			$response = true;
		}

		return $response;
		
	}



	/**
	 * Effectuer votre propre requete personnalisée pour obtenir une ressource
	 * @param string $sql votre requete
	 * @param array $values vos données si vous utilisez une requete préparée
	 * @return array
	*/
	public static function rawQuery(string $req, array $values = array()) {

		$req = self::getDatabase()->query($req, $values, \PDO::FETCH_ASSOC);

		if(\count($req) > 1) {

			$resources = array();

			foreach($req as $e) {
				array_push($resources, $e);
			}

			return $resources;
		} else if(\count($req) == 1) {
			return $req[0];
		} else {
			return array();
		}
		
	}


	/**
	 * Effectuer une correspondance entre la requete spécifiée et l'entité dont le repository est responsable
	 * @param string $values la requete
	 * 
	 * @throws ControllerException
	*/
	private static function validTable($req) {
		
		$tabReq = explode(' ', $req);

		foreach($tabReq as $i => $token) {
			if($tabReq[$i] == strcasecmp($token, "from")) {
				if($tabReq[$i+1] != static::$table)
					throw new ControllerException("Unable to link {$tabReq[$i+1]} table with ".static::class);
			}
		}
	}


	/**
	 * Vérifier si le repository invoqué n'est pas abstrait ou n'est pas associé à une entité valide
	 * 
	 * @throws ControllerException
	 */
	private static function validEntity() {
		if(\is_null(static::$entity))
			throw new ControllerException("Unable to find a valid entity");
	}


}

?>