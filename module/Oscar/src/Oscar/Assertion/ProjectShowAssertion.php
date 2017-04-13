<?php

namespace Oscar\Assertion;

use UnicaenAuth\Assertion\AbstractAssertion;
use Zend\Permissions\Acl\Resource\ResourceInterface;


/**
 * Description of ProjectShowAssertion
 *
 * @author BOUVRY Stephane <stephane.bouvry at unicaen.fr>
 */
class ProjectShowAssertion extends AbstractAssertion
{

    /**
     * Exemple
     */
    protected function assertEntity(ResourceInterface $entity = null, $privilege = null)
    {
        die('YEAR');
//        switch (true) {
//            case $entity instanceof VotreEntite:
//                switch ($privilege) {
//                    case Privileges::VOTRE_PRIVILEGE: // Attention à bien avoir généré le fournisseur de privilèges si vous utilisez la gestion des privilèges d'UnicaenAuth
//                        return $this->assertVotreAssertion($role, $entity);
//                }
//                break;
//        }

        return false;
    }

    /* Vos autres tests */

}