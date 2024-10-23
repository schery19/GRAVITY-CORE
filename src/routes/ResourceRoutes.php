<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\Routing;


abstract class ResourceRoutes {

    /** 
	 * Les routes en GET supportées par la ressource
	 * @var array<Route>
	 */
    public $get = [];

	/** 
	 * Les routes en POST supportées par la ressource
	 * @var array<Route>
	 */
    public $post = [];

	/** 
	 * Les routes en PUT supportées par la ressource
	 * @var array<Route>
	 */
    public $put = [];

	/** 
	 * Les routes en PATCH supportées par la ressource
	 * @var array<Route>
	 */
    public $patch = [];

	/** 
	 * Les routes en DELETE supportées par la ressource
	 * @var array<Route>
	 */
    public $delete = [];


	abstract function __construct();


	public function __set($name, $value) {
		if($name == 'router')
			$this->router = $value;
	}

}


?>
