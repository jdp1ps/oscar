<?php

namespace UnicaenCode\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;


/**
 * Service de configuration d'UnicaenCode
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class Config implements ServiceLocatorAwareInterface
{
    protected $config;

    use ServiceLocatorAwareTrait;



    /**
     * Retourne la liste des répertoires de code
     *
     * @return mixed
     */
    public function getViewDirs()
    {
        $vd = $this->getConfig()['view-dirs'];
        usort($vd, function($a,$b){ // UnicaenCode doit toujours arriver à la fin dans la fusion
            if (false !== strpos($a,'vendor')) $a = 999; else $a = 0;
            if (false !== strpos($b,'vendor')) $b = 999; else $b = 0;
            return $a > $b;
        });
        return $vd;
    }



    /**
     * Retourne la liste des modèles de code
     *
     * @return array
     */
    public function getTemplateDirs()
    {
        $td = $this->getConfig()['template-dirs'];
        usort($td, function($a,$b){ // UnicaenCode doit toujours arriver à la fin dans la fusion
            if (false !== strpos($a,'vendor')) $a = 999; else $a = 0;
            if (false !== strpos($b,'vendor')) $b = 999; else $b = 0;
            return $a > $b;
        });
        return $td;
    }



    /**
     * Retourne le chemin vers lequel seront générés les fichiers (répertoire tmp par défaut)
     *
     * @return string
     */
    public function getGeneratorOutputDir()
    {
        return $this->getConfig()['generator-output-dir'];
    }



    /**
     * Retourne le fichier à inclure pour utiliser SqlFormatter
     *
     * @return string
     */
    public function getSqlFormatterFile()
    {
        return $this->getConfig()['sqlformatter-file'];
    }



    /**
     * Retourne le fichier à inclure pour utiliser Gehsi
     *
     * @return string
     */
    public function getGeshiFile()
    {
        return $this->getConfig()['geshi-file'];
    }



    /**
     * Retourne la liste des espaces de noms des services de l'application
     *
     * @return array
     */
    public function getNamespacesServices()
    {
        return $this->getConfig()['namespaces']['services'];
    }



    /**
     * Retourne la liste des espaces de noms des formulaires de l'application
     *
     * @return array
     */
    public function getNamespacesForms()
    {
        return $this->getConfig()['namespaces']['forms'];
    }



    /**
     * Retourne la liste des espaces de noms des entités de l'application
     *
     * @return array
     */
    public function getNamespacesEntities()
    {
        return $this->getConfig()['namespaces']['entities'];
    }



    /**
     * Retourne la liste des espaces de noms des entités de l'application
     *
     * @return array
     */
    public function getNamespacesHydrators()
    {
        return $this->getConfig()['namespaces']['hydrators'];
    }



    /**
     * Retourne la configuration globale de l'application
     *
     * @return array
     */
    public function getGlobalConfig()
    {
        return $this->getServiceLocator()->get('Config');
    }



    /**
     * Retourne la configuration d'UnicaenCode
     *
     * @return array
     */
    protected function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->getGlobalConfig()['unicaen-code'];
        }

        return $this->config;
    }

}
