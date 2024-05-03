<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\Routing;

use Gravity\Core\App\Resources\AbstractResource;
use Gravity\Core\Exceptions\ControllerException;


class Route extends AbstractResource {

	private $path;
	private $action;
	private $name;

	private $matches;


	public function __construct($path, $action, $name='') {
		$this->path = trim($path, '/');
		$this->action = $action;
		$this->name = $name;
	}


	public function getPath() {
		return $this->path;
	}

	public function getAction() {
		return $this->action;
	}

	public function getName() {
		return $this->name;
	}

	public function getParameters() {
		return $this->getArguments($this->matches);
	}

	public function getMatches() {
		return $this->matches;
	}


	public function matches(string $url) {

		$path = preg_replace('#:([\w]+)#', '([^/]+)', $this->path);

		$pathToMatch = "#^$path$#";

		if(preg_match($pathToMatch, $url, $matches)) {
			$this->matches = $matches;
			return true;
		} else {
			return false;
		}
	}


	/** 
	 * Les autres paramètres et données éventuelles du corps de la requête
	 * @return array l'ensemble des paramètres avec leurs noms
	 */
	public function getExtras() {
		$params = array();

		$i = 0;

		foreach($_REQUEST as $k=>$arg) {

			if($i == 0) {
				$i++;
				continue;
			}

			$params[$k] = $arg;
		}

		return $params;
	}

	
	/** 
	 * Exécute la méthode appropriée du controlleur appelé.
	 * En cas de présence de paramètres dans l'url, elle appelle la
	 * méthode correspondante avec tous les arguments qu'il faut grace
	 * à la fonction invoke de la super classe Controller
	 * 
	 * @param $router l'instance router appelant cette méthode 
	 */
	public function execute($router) {

		$params = null; //explode('@', $this->action);

		if(is_callable($this->action)) {

			$functionName = !is_array($this->action)?(new \ReflectionFunction($this->action)) : 
							(new \ReflectionMethod($this->action[0], $this->action[1]));

			$args = $functionName->getParameters();

			if(!is_array($this->action)) {
				call_user_func_array($this->action, $args);
			} else {
				$methodArgs = (count($this->matches) > 1)?$this->getArguments($this->matches):array();

				$functionName->invokeArgs(new $this->action[0](), $methodArgs);
			}
			
		} else {

			if(is_array($this->action))
				$params = $this->action;
			else 
				$params = explode('@', $this->action);

			try {

				$controller = new $params[0]();
				$method = $params[1];
				$methodArgs = (count($this->matches) > 1)?$this->getArguments($this->matches):null;//Les arguments présents dans l'url

				return $controller->invoke($method, $methodArgs, $router);

			} catch(\Error $e) {//Controlleur ou méthode de controlleur introuvable
				throw new ControllerException($e);
			}
		}
	}


	private function getArguments(array $args) {

		$arguments = array();

		//L'élément d'indice 0 devrait correspondre à l'url tout entier
		for($i = 1; $i<count($args); $i++) {
			$arguments[] = $args[$i];
		}

		return $arguments;
	}


	public function toArray() {
		return [
			'path' => $this->getPath(),
			'action' => $this->getAction(),
			'name' => $this->getName()
		];
	}

}

?>
