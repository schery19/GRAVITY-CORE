<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\Routing;

use Gravity\Core\Exceptions\BadMethodException;
use Gravity\Core\Exceptions\NoRouteException;

class Router {

	private $url;
	private $routes = [];

	
	public function __construct($url) {
		$this->url = trim($url, '/');
	}


	/**
	 * Retourne toutes les routes définies
	 * 
	 * @return array listes des routes
	 */
	public function getRoutes() {
		return $this->routes;
	}


	/**
	 * Retourne une route nommée
	 * @param string $name le nom de la route
	 * @return Route la route trouvée
	 * 
	 * @throws NoRouteException
	 */
	public function getRoute($name) {
		$routeFound = null;

		foreach ($this->routes as $route) {

			foreach($route as $r) {
				if($r->getName() === $name)
					$routeFound = $r;
			}

		}

		if($routeFound == null)
			throw new NoRouteException("No $name route found");

		return $routeFound;
	}


	/**
	 * Définition d'ume route en GET
	 * @param string $path le chemin générique de la route.
	 * @param string|array|callable $action action à exécuter lors du déclenchement de la route
	 * @param string $name le nom de la route (optionnel)
	 */
	public function get(string $path, $action, $name='') {
		$this->routes['GET'][] = new Route($path, $action, $name);
	}

	/**
	 * Définition d'ume route en POST
	 * @param string $path le chemin générique de la route.
	 * @param string|array|callable $action action à exécuter lors du déclenchement de la route
	 * @param string $name le nom de la route (optionnel)
	 */
	public function post(string $path, $action, $name='') {
		$this->routes['POST'][] = new Route($path, $action, $name);
	}

	/**
	 * Définition d'ume route en PUT
	 * @param string $path le chemin générique de la route.
	 * @param string|array|callable $action action à exécuter lors du déclenchement de la route
	 * @param string $name le nom de la route (optionnel)
	 */
	public function put(string $path, $action, $name='') {
		$this->routes['PUT'][] = new Route($path, $action, $name);
	}

	/**
	 * Définition d'ume route en PATCH
	 * @param string $path le chemin générique de la route.
	 * @param string|array|callable $action action à exécuter lors du déclenchement de la route
	 * @param string $name le nom de la route (optionnel)
	 */
	public function patch(string $path, $action, $name='') {
		$this->routes['PATCH'][] = new Route($path, $action, $name);
	}


	public function run() {

		if(!isset($this->routes[$_SERVER['REQUEST_METHOD']]))
			throw new BadMethodException("Method {$_SERVER['REQUEST_METHOD']} not supported");

		foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {

			if($route->matches($this->url))
				return $route->execute($this);
		}

		throw new NoRouteException("Route {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} not found");

	}


}

?>