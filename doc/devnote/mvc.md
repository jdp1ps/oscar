# MVC

Ce document permet de débuter le développement dans l'application Oscar, il ne peut se substituer à une formation Zend3, mais permet malgrès cela de s'en passer.

##Nouveau module Zend3

Cette étape n'est pas forcement necessaire. Si vous souhaitez produire une fonctionnalité supplémentaire

## Créer un nouveau contrôleur

### Emplacement/fichier

Les contrôleurs doivent être placés dans le dossier `module/Oscar/src/Oscar/Controller`. Par convention, le nom du fichier/classe se terminera par le terme **Controller** : 

```php
<?php
// module/Oscar/src/Oscar/Controller/DemoController.php
namespace Oscar\Controller;

class DemoController extends AbstractOscarController
{
	// Foo
}
```

Zend **ne va pas identifier automatiquement** la classe comme un contrôleur, il va falloir :
 - Créer une factory pour livrer une instance de ce contrôleur
 - Configurer Zend pour lui indiquer quelle factory utiliser pour instancier le controleur
 
### Instancier le contrôleur : Factory
 
On cré la factory dans le même dossier que le contrôleur. Par convention, on utilise le même nommage que pour le contrôleur suffixé du terme **Factory**, dans notre cas cela donne : 

```php
<?php
// module/Oscar/src/Oscar/Controller/DemoControllerFactory.php
namespace Oscar\Controller;

use Oscar\Factory\AbstractOscarFactory;
use Interop\Container\ContainerInterface;

class DemoControllerFactory extends AbstractOscarFactory
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$c = new DemoController();
		return $c;
	}
}
```

### Configurer le contrôleur

Le contrôleur doit être identifié dans la configuration du module Oscar `module/Oscar/config/module.config.php` : 

```php
<?php
// module/Oscar/config/module.config.php

// ...

return array( 
	// ...
	'controllers' => array(
		// ...
		'factories' => array(
			// ...
		    	'Demo' => \Oscar\Controller\DemoControllerFactory::class
		),
	)
);
```

### Ajouter une action

Les actions des contrôleurs sont de méthodes publiques dont le nom est suffixé par **Action** : 

```php
<?php
// module/Oscar/src/Oscar/Controller/DemoController.php
namespace Oscar\Controller;

class DemoController extends AbstractOscarController
{
	public function fooAction(){
		return $this->getResponseOk("Bravo !");
	}
}
```

Pour tester cette action, il va falloir : 

 - Créer une Route
 - Autoriser l'accès à cette action
 
### Routage

Les routes sont configurées dans le fichier `module/Oscar/config/route.yml` 

Le systèmes est arborescent, généralement, elles correspondents à un contexte générale (qui correspond au contrôleur), puis des *sous-routes* dans ce contexte (correspondant aux actions).

```ỳaml
demo:
    type: literal
    options:
        route: /demo
        defaults:
            controller: Demo

    may_terminate: false
    child_routes:
        foo:
            type: segment
            options:
                route: /foo
                defaults:
                    action: foo
```

Si vous testez la route à ce moment, vous devrier obtenir une **erreur 403**

### Autorisation d'accès

La dernière chose à configurer est l'autorisation d'accès à l'action du contrôleur dans le fichier `module/Oscar/config/module.config.php` : 

```php
<?php
// module/Oscar/config/module.config.php

// ...

return array( 
	// ...
	'bjyauthorize' => [
		// ...
		'guards' => array(
			UnicaenAuth\Guard\PrivilegeController::class => [
                // ...
                [ 
                    'controller' =>  'Demo',
                    'action' => ['foo', ],	
                    'roles' => [],
                ],
            ]
		),
	]
);
```

A cette étape, vous devrier avoir une action fonctionnelle à l'adresse : http://localhost:8080/demo/foo

## Template de vue

Pour une rendu HTML, Zend est normalisé pour envoyer le retour d'une action (sous la forme d'un tableau indexé) à sont moteur de rendu. Ce dernier va recherche un template PHP dont l'emplacement est par défaut basé sur le nom du controlleur et le nom de l'action dans le dossier `module/Oscar/view/oscar`.

Dans notre exemple, pour l'action **DemoController::fooAction**, le template sera le fichier `module/Oscar/view/oscar/demo/foo.phtml`

En modifiant l'action **fooAction** ainsi : 

```php
<?php
// module/Oscar/src/Oscar/Controller/DemoController.php
namespace Oscar\Controller;

class DemoController extends AbstractOscarController
{
	public function fooAction(){
		return [
			'message' => 'Félicitation !!!'
		];
	}
}
```

Puis en ajoutant le fichier `module/Oscar/view/oscar/demo/foo.phtml` : 

```php
<?php
// module/Oscar/view/oscar/demo/foo.phtml
?>
<div class="container">
	<h1>mesage : <?= $message ?></h1>
</div>
```

### Méthodes de vue usuelles

#### $this->url()
Permet de générer des URL à partir des clefs des routes. Dans notre exemple : 

```php
<?php
// module/Oscar/view/oscar/demo/foo.phtml
?>
<div class="container">
	<h1>mesage : <?= $message ?></h1>
	<p>
		<a href="<?= $this->url('demo/foo') ?>">
			Accès à la page de démo : <?= $this->url('demo/foo') ?>
		</a>	
	</p>
</div>
```

#### $this->grant()
Permet de tester un privilège

```php
<?php
// module/Oscar/view/oscar/demo/foo.phtml
?>
<div class="container">
	<h1>message : <?= $message ?></h1>
	<p>
		<a href="<?= $this->url('demo/foo') ?>">
			Accès à la page de démo : <?= $this->url('demo/foo') ?>
		</a>	
	</p>
	<?php if($this->grant()->privilege(\Oscar\Provider\Privileges::ACTIVITY_INDEX)): ?>
		Vous avez accès à la liste des activités
	<?php else: ?>
		Vous n'avez pas accès à la liste des activités
	<?php endif; ?>
</div>
```

> La méthode **privilege(PRIVILEGE)** est polymorphe, elle peut prendre un objet en deuxième paramètre (Une activité) pour tester un privilège spécifique sur une activité.


## Paramètres de route

## Contrôleur avec service

## Créer un service
