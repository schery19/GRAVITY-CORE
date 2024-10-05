<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\App\Repository;


use Gravity\Core\App\Entity\Entity;
use Gravity\Core\Database\Database;
use Gravity\Core\Exceptions\BadRequestException;
use Gravity\Core\Exceptions\ControllerException;
use Gravity\Core\Exceptions\NotFoundException;



abstract class Repository {

	protected static $entity;
	protected static $db;
	protected static $table;
	protected static $columns = array();

	protected static $primary_key = "";


	protected static function getDatabase($configs = array())
	{
		static::$primary_key = (static::$primary_key == "") ? "id" : static::$primary_key;
		return static::$db = new Database(require("../configs/database.php"));
	}



	/**
	 * Trouver tous les enregistrements
	 * 
	 * @param array|null $columns Colonnes à récupérer
	 * @param string|null $orderClause Colonnes à ordonner
	 * @param string|null $orderArgs type d'ordonancement (ASC ou DESC)
	 * @param int|null $limit Nombre d'enregistrements à récupérer
	 * @param int|null $offset point de départ
	 * 
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
			$entity = new static::$entity($e);
			
			if(array_key_exists(static::$primary_key, $e))
				$entity->setId($e[static::$primary_key]);
			
			
			array_push($entities, $entity);
		}

		return $entities;

	}


	/**
	 * Trouver un enregistrement à partir d'un identifiant unique (l'id)
	 * 
	 * @param mixed $id
	 * 
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
	 * Trouver un enregistrement à partir d'un identifiant unique (l'id)
	 * 
	 * @param mixed $id
	 * 
	 * @return object|null
	 * 
	 * @throws NotFoundException
	*/
	public static function findOrFail($id, $fallingMessage = '') {

		$entity = static::find($id);

		if($fallingMessage == '')
			$fallingMessage = static::$entity.' not found';

		if(is_null($entity))
			throw new NotFoundException($fallingMessage);

		return $entity;
		
	}



	/**
  	 * Trouver un/des enregistrement(s) à partir d'une/des condition(s)
	 * Retourne un tableau d'entités si toutes les conditions ont été vérifiées
	 * Retourne un tableau vide si aucune donnée n'a été trouvée
	 * 
  	 * @param array $whereColums colonnes sur lesquelles les conditions ont été posées
  	 * @param array $values valeurs colonnes conditionnées
	 * @param string|null $orderClause Colonnes à ordonner
	 * @param string|null $orderArgs type d'ordonancement (ASC ou DESC)
	 * @param int|null $limit Nombre d'enregistrements à récupérer
	 * @param int|null $offset point de départ
	 * 
  	 * @return array
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

		if(\count($req) >= 1) {

			$entities = array();

			foreach($req as $e) {
				array_push($entities, (new static::$entity($e))->setId($e[static::$primary_key]));
			}

			return $entities;
		} else {
			return array();
		}
		
	}


	/**
	 * Trouver un/des enregistrement(s) à partir d'une/des condition(s)
	 * Retourne un tableau d'entités si toutes les conditions ont été vérifiées
	 * Retourne un tableau vide si aucune donnée n'a été trouvée
	 * 
	 * @param array $whereColums colonnes sur lesquelles les conditions ont été posées
	 * @param array $values valeurs colonnes conditionnées
	 * @param string|null $orderClause Colonnes à ordonner
	 * @param string|null $orderArgs type d'ordonancement (ASC ou DESC)
	 * @param int|null $limit Nombre d'enregistrements à récupérer
	 * @param int|null $offset point de départ
	 * 
	 * @return array
	 * 
	 * @throws ControllerException
	 */
	public static function findWhereWithOperators(
		array $columns,
		array $values,
		array $operators,
		array $logicalOperators = [], // Ajout de ce paramètre pour les opérateurs logiques
		string $orderClause = null,
		string $orderArgs = null,
		int $limit = null,
		int $offset = null
	) {
		static::validEntity();
		self::getDatabase();

		if (\count($columns) < 1) {
			throw new ControllerException("Column(s) not specified on " . static::class . "::findWhere method");
		}

		if (\count($operators) < 1) {
			throw new ControllerException("Operator(s) not specified on " . static::class . "::findWhere method");
		}

		if (\count($values) < 1) {
			throw new ControllerException("Column(s) value(s) not specified on " . static::class . "::findWhere method");
		}

		if (\count($columns) !== \count($operators) || \count($columns) !== \count($values)) {
			throw new ControllerException("The number of columns, operators, and values must be the same.");
		}

		// Construction de la clause WHERE
		$whereClauseParts = [];
		for ($i = 0; $i < \count($columns); $i++) {
			$column = $columns[$i];
			$operator = $operators[$i];
			if (!in_array($operator, ['=', '!=', '<', '>', '<=', '>='])) {
				throw new ControllerException("Invalid operator: $operator");
			}
			$whereClauseParts[] = "$column $operator ?";
		}

		// Appliquer les opérateurs logiques, avec 'AND' comme valeur par défaut
		if (!empty($logicalOperators)) {
			if (\count($logicalOperators) !== (\count($whereClauseParts) - 1)) {
				throw new ControllerException("The number of logical operators must be one less than the number of conditions.");
			}
			$whereClause = $whereClauseParts[0];
			for ($i = 0; $i < \count($logicalOperators); $i++) {
				$whereClause .= ' ' . strtoupper($logicalOperators[$i]) . ' ' . $whereClauseParts[$i + 1];
			}
		} else {
			// Utilisation de 'AND' comme opérateur logique par défaut
			$whereClause = implode(' AND ', $whereClauseParts);
		}

		// Construction des chaînes ORDER BY, LIMIT et OFFSET
		$orderString = (!\is_null($orderClause) && !\is_null($orderArgs)) ? "ORDER BY $orderClause $orderArgs" : "";
		$limitString = (!\is_null($limit)) ? "LIMIT " . $limit : "";
		$offsetString = (!\is_null($offset)) ? "OFFSET " . $offset : "";

		// Préparation et exécution de la requête
		$sql = "SELECT * FROM " . static::$table . " WHERE " . $whereClause . " " . $orderString . " " . $limitString . " " . $offsetString;
		$req = self::getDatabase()->query($sql, $values, \PDO::FETCH_ASSOC);

		if (\count($req) >= 1) {
			$entities = [];
			foreach ($req as $e) {
				$entities[] = (new static::$entity($e))->setId($e[static::$primary_key]);
			}
			return $entities;
		} else {
			return [];
		}
	}



	/**
  	 * Trouver un/des enregistrement(s) à partir d'une/des condition(s)
	 * Retourne un tableau d'entités si au moins l'une des conditions est vérifiée
	 * Retourne un tableau vide si aucune donnée n'a été trouvée
	 * 
  	 * @param array $whereColums colonnes sur lesquelles les conditions ont été posées
  	 * @param array $values valeurs colonnes conditionnées
	 * @param string|null $orderClause Colonnes à ordonner
	 * @param string|null $orderArgs type d'ordonancement (ASC ou DESC)
	 * @param int|null $limit Nombre d'enregistrements à récupérer
	 * @param int|null $offset point de départ
	 * 
  	 * @return array
	 * 
	 * @throws ControllerException
  	*/
	public static function findOrWhere(array $whereColums, array $values, string $orderClause = null, string $orderArgs = null, int $limit = null, int $offset = null) {

		static::validEntity();

		self::getDatabase();

		if(\count($whereColums) < 1)
			throw new ControllerException("Column(s) not specified on ".static::class."::findOrWhere method");

		if(\count($values) < 1)
			throw new ControllerException("Column(s) value(s) not specified on ".static::class."::findOrWhere method");

		$whereClause = (\count($whereColums) < 1)?static::$primary_key."=?":\implode('=? or ', $whereColums);

		$whereClause .= (\count($whereColums) > 0)?'=?':'';

		$orderString = (!\is_null($orderClause) && !\is_null($orderArgs)) ? "order by $orderClause $orderArgs" : "";

		$limitString = (!\is_null($limit)) ? "limit ".$limit : "";

		$offsetString = (!\is_null($offset)) ? "offset ".$offset : "";

		$req = self::getDatabase()->query("select * from ". static::$table ." where ". $whereClause . " $orderString $limitString $offsetString", $values, \PDO::FETCH_ASSOC);

		if(\count($req) >= 1) {

			$entities = array();

			foreach($req as $e) {
				array_push($entities, (new static::$entity($e))->setId($e[static::$primary_key]));
			}

			return $entities;
		} else {
			return array();
		}
		
	}


	/**
	 * Effectuer votre propre requete personnalisée pour obtenir une/des entité(s)
	 * 
	 * @param string $req votre requete personnalisée
	 * @param array $values vos données si vous utilisez une requete préparée
	 * @param string|null $orderClause Colonnes à ordonner
	 * @param string|null $orderArgs type d'ordonancement (ASC ou DESC)
	 * @param int|null $limit Nombre d'enregistrements à récupérer
	 * @param int|null $offset point de départ
	 * 
	 * @return array
	 * 
	 * @throws ControllerException
	*/
	public static function fromQuery(string $req, array $values = array(), string $orderClause = null, string $orderArgs = null, int $limit = null, int $offset = null) {

		static::validEntity();

		static::validTable($req);

		$orderString = (!\is_null($orderClause) && !\is_null($orderArgs)) ? "order by $orderClause $orderArgs" : "";

		$limitString = (!\is_null($limit)) ? "limit ".$limit : "";

		$offsetString = (!\is_null($offset)) ? "offset ".$offset : "";

		$req = self::getDatabase()->query($req." $orderString $limitString $offsetString", $values, \PDO::FETCH_ASSOC);

		if(\count($req) >= 1) {

			$entities = array();

			foreach($req as $e) {
				array_push($entities, (new static::$entity($e))->setId($e[static::$primary_key]));
			}

			return $entities;
		} else {
			return array();
		}
		
	}


	/**
	 * Effectuer un enregistrement
	 * 
	 * @param Entity $entity l'entité à persister dans la base
	 * 
	 * @return bool|int|null
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
	 * 
	 * @param array $data les nouvelles données à persister dans la base
	 * @param mixed $where l'id correspondant
	 * 
	 * @return bool|null
	 * 
	 * @throws BadRequestException
	 */
	public static function update(Entity $entity)
	{

		static::validEntity();

		try {

			self::getDatabase();

			// On vérifie que tous les champs obligatoires sont renseignés
			if (\count((new static::$entity())->getRequiredColumns()) > 0) {
				foreach ((new static::$entity())->getRequiredColumns() as $r) {
					if (!array_key_exists($r, $entity->toArray())) {
						throw new BadRequestException("{$r} field required");
					}
				}
			}

			// Récupérer l'ID de l'entité (ou la clé primaire)
			$id = (array_key_exists(static::$primary_key, $entity->toArray()))
				? $entity->toArray()[static::$primary_key]
				: $entity->toArray()['id'];

			// Définir l'ID dans l'entité
			$entity = $entity->setId($id);

			$dataArray = $entity->toArray();
			unset($dataArray['id']); // On ne met pas à jour l'ID

			// Générer la requête SQL pour l'UPDATE
			$updateFields = [];
			foreach ($dataArray as $column => $value) {

				if(!in_array($column, static::getColumns())) {
					unset($dataArray[$column]);
					continue;
				}

				$updateFields[] = "{$column} = ?";
			}

			$sql = "UPDATE " . static::$table . " SET " . \implode(", ", $updateFields) . " WHERE " . static::$primary_key . " = ?";

			// Ajouter l'ID à la fin des données à passer à la requête
			$dataArray[] = $id;

			$req = self::getDatabase()->exec($sql, array_values($dataArray));

			return $req;

		} catch (BadRequestException $e) {
			throw new BadRequestException($e->getMessage());
		} catch (\Exception $e) {
			throw new \Exception($e);
		}
	}



	/**
	 * Effectuer votre propre requete personnalisée pour obtenir une ressource
	 * 
	 * @param string $sql votre requete
	 * @param array $values vos données si vous utilisez une requete préparée
	 * 
	 * @return array
	*/
	public static function rawQuery(string $req, array $values = array()) {

		$req = self::getDatabase()->query($req, $values, \PDO::FETCH_ASSOC);

		if(\count($req) >= 1) {

			$resources = array();

			foreach($req as $e) {
				array_push($resources, $e);
			}

			return $resources;
		} else {
			return array();
		}
		
	}


	/**
	 * Effectuer une correspondance entre la requête spécifiée et l'entité dont le repository est responsable
	 * 
	 * @param string $values la requete
	 * 
	 * @throws ControllerException
	 */
	private static function validTable($req)
	{

		$tabReq = explode(' ', $req);

		foreach ($tabReq as $i => $token) {
			if ($tabReq[$i] == strcasecmp($token, "from")) {
				if ($tabReq[$i + 1] != static::$table)
					throw new ControllerException("Unable to link {$tabReq[$i + 1]} table with " . static::class);
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


	/**
	 * Récupérer la liste des colonnes de la table associée à l'entité du repository correspondant
	 * 
	 * @return array
	 * 
	 * @throws ControllerException
	 */
	public static function getColumns() {

		static::validEntity();

		$req = self::getDatabase()->query("DESC ". static::$table);

		$fields = [];

		foreach($req as $result) {
			$fields[] = $result->Field;
		}

		return $fields;
	}


	/**
	 * Récupérer les informations de la table associée à l'entité du repository correspondant
	 * 
	 * @return array
	 * 
	 * @throws ControllerException
	 */
	public static function table() {

		static::validEntity();

		$req = self::getDatabase()->query("DESC ". static::$table);

		return $req;
	}


	/**
	 * Récupérer le datetime courant en fonction du serveur de données utilisé
	 * 
	 * @return string datetime courant
	 */
	public static function currentDateTime() {
		$currentDateTime = Repository::rawQuery("SELECT CURRENT_TIMESTAMP() as current_date_time")[0];

        return $currentDateTime['current_date_time'];
	}


	/**
	 * Initier une transactions sql
	 * 
	 * @return bool
	 */
	public static function beginTransaction() {
		return self::$db->getDB()->beginTransaction();
	}


	/**
	 * Valider une transaction sql
	 * 
	 * @return bool
	 */
	public static function commit() {
		return self::$db->getDB()->commit();
	}


	/**
	 * Annuler une transaction sql
	 * 
	 * @return bool
	 */
	public static function rollback() {
		return self::$db->getDB()->rollBack();
	}


}

?>
