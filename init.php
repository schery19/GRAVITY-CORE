<?php

//App
##############################################################################
//Controleurs 
require __DIR__.'/src/app/Controllers/Controller.php';

//Entity
require __DIR__.'/src/app/Entity/Entity.php';

//Repository
require __DIR__.'/src/app/Repository/Repository.php';

//Resources
require __DIR__.'/src/app/Resources/AbstractResource.php';
##############################################################################


//Database
##############################################################################
require __DIR__.'/src/database/Database.php';
##############################################################################


//Exceptions
##############################################################################
require __DIR__.'/src/Exceptions/BadRequestException.php';
require __DIR__.'/src/Exceptions/ControllerException.php';
require __DIR__.'/src/Exceptions/NoRouteException.php';
require __DIR__.'/src/Exceptions/NotFoundException.php';
require __DIR__.'/src/Exceptions/RenderException.php';
##############################################################################


//Routes
##############################################################################
require __DIR__.'/src/routes/Route.php';
require __DIR__.'/src/routes/Router.php';
##############################################################################





?>