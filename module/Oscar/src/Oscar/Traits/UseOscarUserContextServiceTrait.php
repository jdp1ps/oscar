<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Oscar\Service\OscarUserContext;

trait UseOscarUserContextServiceTrait
{
    /**
     * @var OscarUserContext
     */
    private $oscarUserContextService;

    /**
     * @param OscarUserContext $s
     */
    public function setOscarUserContextService( OscarUserContext $oscarUserContextService ) :void
    {
        $this->oscarUserContextService = $oscarUserContextService;
    }

    /**
     * @return OscarUserContext
     */
    public function getOscarUserContextService() :OscarUserContext {
        return $this->oscarUserContextService;
    }

    /**
     * @return Person
     * @throws OscarException
     */
    public function getCurrentPerson(){
        $person = $this->getOscarUserContext()->getCurrentPerson();
        if( !$person ){
            throw new OscarException(_("Votre compte n'est associé à aucune personne"));
        }
        return $person;
    }
}