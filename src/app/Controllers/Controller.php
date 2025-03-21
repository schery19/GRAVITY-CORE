<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\App\Controllers;


use Gravity\Core\App\Resources\AbstractResource;
use Gravity\Core\Routing\Route;
use Gravity\Core\Exceptions\BadMethodException;
use Gravity\Core\Exceptions\BadRequestException;
use Gravity\Core\Exceptions\NoRouteException;
use Gravity\Core\Exceptions\RenderException;
use Gravity\Core\Routing\Router;


class Controller {

	protected $router;
	

	public function __construct($router = null) {
		$this->router = $router;
	}


	public function render($data, $code = 200) {
		header("HTTP/1.1 {$code}");
		echo $data;
	}


	public function renderJson($data, $code = 200) {
		header("Content-Type: application/json");

		$out = array();

		if($data instanceof AbstractResource) {

			$out = $data->toRender();

		} else {

			$out = $this->fetchData($data);
		}

		$this->render(json_encode($out, JSON_PRETTY_PRINT), $code);

	}


	private function fetchData($arr = array()) {
		$result = array();

		foreach($arr as $key=>$value) {

			if($value instanceof AbstractResource) {
				$value = $value->toRender();
			}

			if(is_array($value)) {
				$result[$key] = $this->fetchData($value);
			} else {
				$result[$key] = $value;
			}
			
		}

		return $result;
	}


	/**
	 * Affichage d'ume vue
	 * 
	 * @param string $path le chemin de la vue.
	 * Si la vue se situe dans un dossier, utilisez '.' au lieu de '/' comme séparateur
	 * @param string $layout le gabarit de la vue s'il y en a.
	 * Si le gabarit se situe dans un dossier, utilisez '.' au lieu de '/' comme séparateur
	 * @param array $data les données à injecter dans la vue s'il y en a
	 * 
	 * @throws RenderException Si la vue ou le gabarit est introuvable
	 */
	public function renderView(string $path, string $layout = null, array $data = null) {

		if(!is_string($path)) {
			throw new RenderException("No view found for {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} route");
		}

		$path = str_replace('.', DIRECTORY_SEPARATOR, $path);

		if(!is_null($layout))
			$layout = str_replace('.', DIRECTORY_SEPARATOR, $layout);

		if(!file_exists(VIEWS.$path.'.php'))
			throw new RenderException("View '{$path}.php' not found for {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} route !");
		if(!is_null($layout) && !file_exists(VIEWS.$layout.'.php'))
			throw new RenderException("Layout '{$layout}.php' not found for {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} route !");

		ob_start();

		if($data)
			extract($data);

		require VIEWS.$path.'.php';

		$content = ob_get_clean();

		!is_null($layout)?require VIEWS.$layout.'.php':require VIEWS.$path.'.php';

	}


	/**
	 * Générer un lien directement à partir d'une vue
	 * 
	 * @param string $url l'url à générer
	 * 
	 * @return string
	 */
	public function generateUrl(string $url) {
		return trim($url);
	}


	/**
	 * Exécuter dynamiquement une fonction avec ses éventuels arguments
	 * 
	 * @param string $methodName le nom de la méthode du controleur exécuter
	 * @param Router $router le router qui exécute le controleur
	 * @param array $arguments les éventuels arguments de la méthode
	 * @throws \Gravity\Core\Exceptions\ControllerException
	 * 
	 * @return mixed
	 */
	public function invoke(string $methodName, Router $router, $arguments = array()) {

		$this->router = $router;

		$method = (new \ReflectionMethod($this, $methodName));

		return !empty($arguments)?$method->invokeArgs($this, $arguments):$this->$methodName();
		
	}
	

	public function redirect($destination) {
		header('Location: '.$destination);
	}


	/**
	 * Générer un lien à partir d'une route nommée
	 * 
	 * @param string $name le nom de la route.
	 * @param array $values valeurs des paramètres si l'url en contient
	 * 
	 * @return string|null l'url
	 * 
	 * @throws BadMethodException|BadRequestException|NoRouteException
	 */
	public function route($name, $values = array(), $absolute = false) {

		$found = false;

		if(!isset($this->router->getRoutes()[$_SERVER['REQUEST_METHOD']]))
			throw new BadMethodException("Method {$_SERVER['REQUEST_METHOD']} not supported");

		foreach($this->router->getRoutes()[$_SERVER['REQUEST_METHOD']] as $r) {
			if($r->getName() == $name){
				$r->matches($r->getPath());
				$args = $r->getArguments($r->getMatches());

				if(\count($args) > \count($values)) {
					throw new BadRequestException("Parameters missed for {$r->getName()} route");
				}

				$params = array();

				$i = 0;

				foreach($args as $a) {
					$params[$a] = $values[$i++];
				}
				
				$finalUrl = preg_replace_callback('/:(\w+)/', function($matches) use ($params) {
					$key = $matches[0];
					return isset($params[$key])?$params[$key]:$key;
				}, $r->getPath());

				$found = true;

				$base_uri = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];

				return ($absolute)?$base_uri.'/'.$finalUrl:$finalUrl;

			}

		}

		if(!$found) {
			throw new NoRouteException("Route named {$name} not found");
		}
	}


	
	public function __get($name) {
		/**
		 * Retourne la route exécutant le controleur
		 * 
		 * @return Route la route trouvée
		 */
		if($name == 'currentRoute')
			return $this->router->getCurrentRoute();
	}

}

?>
