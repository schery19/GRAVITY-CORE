# GRAVITY-CORE
Bibliothèque principale pour le framework gravity, vous pouvez l'utiliser aussi pour votre propre projet moyennant que ce dernier respecte quelques dépendances.

## Installation
### Via composer
Utilisez [composer](https://getcomposer.org/download/) pour installer GRAVITY-CORE, dans la racine de votre projet tapez la commande suivante :

```bash
composer require gravity-framework/gravity-core
```

### Manuellement
Vous pouvez aussi télécharger directement le code source sur le [dépot]() github et décompresser le dossier dans la racine de votre projet.<br/><br/>
Vous devez utiliser quand même composer pour utiliser le core. En vous positionnant dans le dossier décompressé, tapez la commande suivante :

```php
composer dump-autoload
```

Dans le fichier <b>autoload.php</b> dans le dossier vendor de votre projet ajouter la ligne suivante au debut du fichier

```php
require '../GRAVITY-CORE-main/vendor/autoload.php';
```


## Dépendances
Pour utiliser GRAVITY-CORE, vous pouvez soit utiliser [gravity-framework](https://github.com/schery19/gravity-framework) ou faire en sorte que la structure de votre projet réponde à quelques critères :

<ul>
<li>
Dossier <strong>configs</strong> contenant au moins deux fichiers : <strong>configs.php</strong>, <strong>database.php</strong><br/><br/>

Le fichier configs.php permettent de faire les liens entre les divers scripts tels que : php, javascript, css (présents dans un dossier spécifique par exemple : public) ou même des dossiers images et autres de votre projet.

configs.php :
```php
<?php

//A modifier selon la configuration des dossiers du projet
define('DS', DIRECTORY_SEPARATOR);//Séparateur de dossier selon l'OS
define('VIEWS', dirname(__DIR__). DS .'templates'.DS);//Les maquettes
define('SCRIPTS', dirname($_SERVER['SCRIPT_NAME']).DS);//Le dossier dans lequel se trouve votre fichier index.php (contrôleur frontal), par exemple public/
define('STYLES', SCRIPTS.'css'.DS);//Les fichiers css
define('JS', SCRIPTS.'js'.DS);//Les fichiers javascript
define('IMAGES', SCRIPTS.'images'.DS);//Les images

//Vous pouvez aussi définir vos propres constantes selon vos besoins

?>
```

<br/><br/>

Le fichier database.php sert à renseigner les différentes configurations de votre base de données si vous en utilisez

database.php
```php
<?php

return [
    "server" => "mysql",
    "host" => "localhost",
    "port" => "3306",
    "user" => "root",
    "pass" => "",
    "dbname" => "mabase"
]

?>
```
</li><br/>

<li>
Fichier <strong>.htaccess</strong> (optionnel), pour utiliser au mieux le systême de routes

```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f

RewriteRule ^(.*)$ public/index.php?route=$1 [QSA,L]
```
si par exemple vous voulez utiliser ```route``` comme paramètre dans l'url pour vos points d'entrée pour gérer vos différentes routes
</li><br/>

<li>Dossier <strong>templates</strong> (pour des projets web), qui contiendra vos différentes vues et gabarits. vous pouvez les classer en sous-dossiers selon vos différents modules de votre application.<br/><br/>

Exemple d'un gabarit :
```php
<!DOCTYPE html>
<html lang="fr">

    <head>
        <title><?= $title ?></title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="apple-touch-icon" href="<?= IMAGES.'apple-icon.png'?>">
        <link rel="shortcut icon" type="image/x-icon" href="<?= IMAGES.'favicon.ico' ?>">

        <link rel="stylesheet" type="text/css" href="<?= STYLES.'bootstrap.min.css' ?>">
        <link rel="stylesheet" type="text/css" href="<?= STYLES.'custom.css' ?>">
    </head>

    <body>
        
        <div class="container-fluid">
            <?= $content ?>
        </div>

        <!-- Start Script -->
        
        <script src="<?= JS.'jquery-1.11.0.min.js' ?>"></script>
        <script src="<?= JS.'bootstrap.bundle.min.js' ?>"></script>
        <script src="<?= JS.'custom.js' ?>"></script>

        <!-- End Script -->
    </body>

</html>
```

Vous pouvez constater la partie qui varie du gabarit est déclarée en php par la variable ```$content```<br/><br/>

Exemple d'une vue qui descend du gabarit :
```php
<?php $title = "Accueil" ?>

<center><h1>Bienvenue</h1></center>

<p>Lorem ipsum dolor sit atmet consectur ...</p>
```

Voir dans l'[exemple]() d'un projet comment utiliser les templates avec gravity.
</li>

</ul>

### Note :
En utlisant [gravity-framework](https://github.com/schery19/gravity-framework), la structure récommandée pour votre projet est automatiquement respectée

## Routes
Dans le fichier principal de votre projet, par exemple index.php, vous utilisez l'objet ```Router``` pour déclarer vos routes.

Exemple :
```php
require '../vendor/autoload.php';
require '../config/configs.php';

use Gravity\Core\App\Controllers\Controller;
use Gravity\Core\Routing\Router;


$router = new Router($_REQUEST['route']);

########################################### Definissez vos routes ########################################### 

$router->get('/', function() {
	(new Controller())->renderView('Home.index', 'layout');
});

$router->get('/shop', [ShopController::class, 'index']);

$router->get('/shop/:productId', "App\Controllers\ShopController@show");

```

Dans cet exemple, on a déclaré trois routes de trois maniêres différentes.

```php
$router->get('/', function() {
	(new Controller())->renderView('Home.index', 'layout');
});
```
Permet de déclarer une route en spécifiant un chemin, une fonction (closure) qui exprime l'action à effectuer lors du déclenchement de la route.<br/>
Dans l'action on affiche une vue <b>index</b> présente dans le dossier ```templates/Home```, cette vue est basée sur un gabarit nommé <b>layout</b> présent à la racine du dossier templates.<br/><br/>

```php
$router->get('/shop', [ShopController::class, 'index']);

$router->get('/shop/:productId', "App\Controllers\ShopController@show");
```

Deux autres moyens de déclarer une route, cette fois-ci en utilisant des méthodes de controleur

Voir [gravity-framework](https://github.com/schery19/gravity-framework) pour plus de détails sur comment utiliser les routes et les controleurs avec gravity.

Il faut penser au final à démarrer le router dans un bloc <b>try/catch</b> :

```php
try {
	$router->run();
} catch(Exception $e) {
    	//Affiche les détails de l'erreur dans une vue
	(new Controller())->renderView('Errors.index', 'Errors.layout', ['error'=>$e]);
}
```

## Base de données
Si vous utilisez une base de données, assurez-vous que les configurations sont correctes dans le fichier ```database.php``` présent dans le dossier <b>configs</b>.

### Entity

Pour chaque table que vous utiliserez vous créerez une classe d'entité correspondante.<br/>

Par exemple pour une table <b>articles</b> avec des colonnes suivantes :
<ul>
<li>id (clé primaire)</li>
<li>titre</li>
<li>contenu</li>
<li>auteur</li>
<li>date_publication</li>
<li>date_modification</li>
<li>commentaires</li>
</ul>

Vous aurez une classe ```Article``` dérivée de la classe ```Gravity\Core\App\Entity\Entity``` avec uniquement les champs requis :


```php
use Gravity\Core\App\Entity\Entity;

class Article extends Entity {

    protected static $required = [
        'titre',
        'contenu',
        'auteur',
        'date_publication'
    ];

}
```

<b>Note</b> : Inutile de préciser l'attribut ```id```, puisque gravity prend en charge automatiquement les clés primaires.

### Repository

Pour chaque classe d'entité créée, il faut une classe de type ```Gravity\Core\App\Repository\Repository```, pour communiquer effectivement avec la base à tout moment.

Classe <b>ArcticleRepository</b> :
```php
use Gravity\Core\App\Repository\Repository;


class ArticleRepository extends Repository {

    protected static $entity = Arcticle::class;
    protected static $table = "articles";

}
```

<b>Attention</b> : Si la clé primaire de votre table porte un nom différent de <b>id</b>, vous devez absolument ajouter le champ statique ```$primary_key``` avec comme valeur le nom de la clé de la manière suivante :<br/><br/>
```protected static $primary_key = "article_id";```.


### Récupération et maipulation des données
Vous pouvez maintenant manipuler ou récupérer les données soit à travers un controleur ou directement lors de la définition des routes par exemple pour le rendre à l'utilisateur.

Exemple d'utilisation
```php
$router->get('/', function() {
    	//Tous les articles
    	$articles = ArticleRepository::findAll();
	(new Controller())->renderView('Home.index', 'layout', ['articles'=>$articles]);
});

$router->get('/articles/:id', function() use($router) {
    	//Les paramètres de la route pour récupérer l'id
    	$params = $router->getRoute('articles.get')->getParameters();

	$a = ArticleRepository::find($params[0]);

	(new Controller())->renderView('Articles.show', 'layout', ['article'=>$a]);

}, 'articles.get');

$router->get('/articles/author/:name', function() use($router) {
    	//Les paramètres de la route pour récupérer l'auteur
    	$params = $router->getRoute('articles.get.author')->getParameters();

	$a = ArticleRepository::findWhere(['auteur'], [$params[0]]);

	(new Controller())->renderView('Articles.show', 'layout', ['article(s)'=>$a]);

}, 'articles.get.author');

$router->post('/articles', function() use($router) {
    	//Les paramètres et données de la requête
    	$params = $router->getRoute('articles.post')->getExtras();

    	$a = new Article($params);

	$saved = ArticleRepository::save($a);

	(new Controller())->renderView('Articles.save', 'layout', ['saved'=>$saved]);

}, 'articles.post');

```

Les méthodes ```find()```, ```findAll()```, ```save()``` permettent de manipuler une entité à travers son <b>repository</b> correspondant, il y en a d'autres méthodes, référez vous à [gravity-framework](https://github.com/schery19/gravity-framework) pour avoir un idée plus claire sur leur utilisation.


### Ressources
Il est possible de récupérer les données selon une structure différente de celle de la base, et même les modifier partiellement lors du rendu.

Pour cela il faut créer une classe resource dérivée de ```Gravity\Core\App\Resources\AbstractResource``` adaptée à votre entité.

Pour notre exemple article, on pourrait le formater pour afficher rédacteur au lieu d'auteur, on pourrait aussi afficher seulement les 20 premiers caractères du contenu. pour cela on implémente la méthode ```toArray()``` en retournant un tableau renfermant votre nouvelle structure

Illustration :
```php
use Gravity\Core\App\Resources\AbstractResource;

class ArticleResource extends AbstractResource {

    protected $entity = Article::class;

    public function toArray() {
        $data = $this->entity->toArray();

        unset($data['auteur']);
        
        $data['rédacteur'] = $this->entity->auteur;

        $data['contenu'] = substr($this->entity->contenu, 0, 20).'...';

        return $data;
    }

}
```

Vous pourriez aussi formater la date de publication selon vos besoins.

A l'affichage vous utilisez l'instance de la classe resource créée pour rendre les données formatées

```php
// Article dont l'id est 5
$article = ArticleRepository::find(5);

$articleFormatted = (new ArticleResource())->make($article);

(new Controller())->renderView('Articles.show', 'layout', ['article'=>$articleFormatted]);
```

<b>Attention</b> : Pour formatter un tableau d'entités, vous utilisez la méthode ```collection()``` au lieu de ```make()```


## Licences
<b>GRAVITY-CORE</b> est publiée sous licence MIT, voir le fichier LICENSE.txt ou visiter [http://www.opensource.org/licenses/mit-license.php](http://www.opensource.org/licenses/mit-license.php) pour plus de détails


## Contributions
Toutes les contributions sont les bienvenues en vue d'améliorer la librairie et le [framework](https://github.com/schery19/gravity-framework), selon les rêgles, respect et courtoisie


## Extra 
N'hésitez pas à reporter vos problèmes dans la section [issues](https://github.com/schery19/gravity-core/issues), pour une meilleure communication et contribuer le plus possible à l'avancement du projet

