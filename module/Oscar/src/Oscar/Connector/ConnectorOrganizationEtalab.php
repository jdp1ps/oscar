<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18-01-11 15:10
 * @copyright Certic (c) 2018
 */

namespace Oscar\Connector;


use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Exception\ConnectorException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;

class ConnectorOrganizationEtalab extends ConnectorOrganizationJSON implements ServiceLocatorAwareInterface
{
    use ConnectorParametersTrait;


    private $serviceLocator;

    const CONNECTOR_NAME = "etatlab";



    /**
     * ConnectorOrganizationEtalab constructor.
     */
    public function __construct()
    {
        parent::__construct(null, null, CONNECTOR_NAME);
    }

    static function getName()
    {
        return self::CONNECTOR_NAME;
    }


    /**
     * @return OrganizationRepository
     */
    public function getRepository()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Organization::class);
    }


    public function execute( $force = false)
    {
        return $this->syncAll();
    }


    public function init(ServiceManager $sm, $configFilePath=null)
    {
        $this->setServiceLocator($sm);
        $this->loadParameters($configFilePath);
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getUrl(){
        return $this->getParameter('url_organizations');
    }

    /**
     * @return ServiceManager
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function getJsonData()
    {
        $url = $this->getUrl();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5); //timeout in seconds
        $return = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if( false === $return ){
            throw new ConnectorException(sprintf("Le connecteur %s n'a pas fournis les données attendues (Erreur : '%s')", $this->getName(), $error));
        }

        $json = json_decode($return);
        $output = [];
        foreach( $json as $data ) {

            $etab = new \stdClass();
            $etab->uid = $data->recordid;
            $etab->shortname = $data->fields->uo_lib;
            $etab->longname = $data->fields->com_nom;
            $etab->code = $data->fields->uai;
            $etab->phone = "";
            $etab->description = "Imported from data.gouv";
            $etab->code = $data->fields->uai;
            // Adresse
            $etab->address = new \stdClass();
            $etab->address->address1 =  $data->fields->adress_uai;
            $etab->address->address2 =  "";
            $etab->address->city =      $data->fields->localite_acheminement_uai;
            $etab->address->zipcode =   $data->fields->code_postal_uai;
            $etab->address->country =   $data->fields->pays_etranger_acheminement;
            $etab->address->addresse3 = "";
            $output[] = $etab;
        }
        return $output;
    }


    public function getEntityManager()
    {
        return  $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }


}