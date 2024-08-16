<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\App\Entity;


use Gravity\Core\App\Resources\AbstractResource;


abstract class Entity extends AbstractResource {

    protected $id;

	protected $data;

	protected static $masks = array(); //Champs ignorés

	protected static $required = array(); //Champs obligatoires


	public function __construct($data = array()) {

		$this->entity = static::class;

		$this->data = $data;

		if(\count($data) > 0) 
			$this->hydrate($data);
		
	}


	public function getRequiredColumns() {
		return static::$required;
	}

	public function getMaskedColumns() {
		return static::$masks;
	}

	public function getColumns() {
		$reflectionClass = new \ReflectionClass(static::class);
		$properties = $reflectionClass->getProperties();

		$columns = array();

		foreach ($properties as $property) {
			$columns[] = $property->getName();
		}

		return $columns;
	}

	private function hydrate(array $data) {

		$this->data = $data;

		foreach ($data as $k => $v) {
			$method = 'set'.ucfirst($k);

			if(method_exists($this, $method)) {
				$this->$method($v);
			} else {
				$this->data[$k] = $this->$k;
			}

		}

	}
	

	private function refresh(array $data) {
		$out = array();

		if(!isset($data['id']))
			$data['id'] = $this->getId();

		foreach ($data as $k => $v) {
			$out[$k] = $v;
		}

		if(\count(static::$masks) > 0) {

			foreach(static::$masks as $m) {
				if(array_key_exists($m, $out))
					unset($out[$m]);
			}

		}

		return $out;
	}


	public function toArray() {
		return $this->refresh($this->data);
	}
	

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param mixed $id 
	 * @return self
	 */
	public function setId($id): self {
		$this->id = $id;
		return $this;
	}


	public function __get($name) {
		return $this->data[$name]??null;
	}


	public function __set($name, $value) {
		$this->data[$name] = $value;

		return $this;
	}


}


?>
