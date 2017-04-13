<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 29/05/15 10:15
 * @copyright Certic (c) 2015
 */

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Application;

ini_set('display_errors', true);
chdir(__DIR__);

$previousDir = '.';

while (!file_exists('config/application.config.php')) {
    $dir = dirname(getcwd());

    if ($previousDir === $dir) {
        throw new RuntimeException(
            'Unable to locate "config/application.config.php": ' .
            'is DoctrineModule in a subdir of your application skeleton?'
        );
    }

    $previousDir = $dir;
    chdir($dir);
}

if (!(@include_once __DIR__ . '/../vendor/autoload.php') && !(@include_once __DIR__ . '/../../../autoload.php')) {
    throw new RuntimeException('Error: vendor/autoload.php could not be found. Did you run php composer.phar install?');
}

$application = Application::init(include 'config/application.config.php');

/* @var $cli \Symfony\Component\Console\Application */
$cli = $application->getServiceManager()->get('doctrine.cli');

/**
 * Ajout par rapport à 'doctrine/doctrine-module/bin/doctrine-module.php' original.
 *
 * Le nom de l'entity manager à utiliser doit être spécifié dans le shell avant l'appel à la commande
 * Doctrine, exemple en bash :
 *      $ export em="orm_radius"
 *      $ vendor/bin/doctrine-module-em dbal:run-sql "select count(*) from radacct"
 */
$em = $application->getServiceManager()->get('doctrine.entitymanager.' . getenv('em'));
$helperSet = $cli->getHelperSet();
$helperSet->set(new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()), 'db');
$helperSet->set(new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em), 'em');
/**
 * Fin de l'ajout.
 */

$cli->run();
