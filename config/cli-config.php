<?php
require 'vendor/autoload.php';

$conf = require __DIR__.'/../config/application.config.php';
$app = \Laminas\Mvc\Application::init($conf);
$sm = $app->getServiceManager();
$entityManager = $sm->get('Doctrine\ORM\EntityManager');

$config = new \Doctrine\Migrations\Configuration\Migration\PhpFile('migrations.php');
return \Doctrine\Migrations\DependencyFactory::fromEntityManager($config, new \Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager($entityManager));