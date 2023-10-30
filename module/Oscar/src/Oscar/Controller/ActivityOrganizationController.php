<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 10/11/15 11:18
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Tackable;

/**
 * Class ActivityOrganizationController
 * @package Oscar\Controller
 * @deprecated
 */
class ActivityOrganizationController extends AbstractOscarController
{
    protected function getActivityOrganization( $id )
    {
        return $this->getEntityManager()->getRepository(ActivityOrganization::class)->find($id);
    }

    /**
     * Suppression d'un partenaire d'une activité.
     */
    public function deleteAction()
    {
        $currentPerson = $this->getCurrentPerson();

        try {
            /** @var ActivityOrganization $activityOrganization */
            $activityOrganization = $this->getActivityOrganization($this->params()->fromRoute('idenroled'));
            $activityId = $activityOrganization->getActivity()->getId();
            $activityOrganization->setDateDeleted(new \DateTime())
                ->setStatus(Tackable::STATUS_DELETE)
                ->setDeletedBy($currentPerson);


            $this->getActivityLogService()->addUserInfo(
                sprintf("a supprimé %s de l'activité %s", $activityOrganization->getOrganization()->log(), $activityOrganization->getActivity()->log()),
                $this->getDefaultContext(),
                $activityOrganization->getActivity()->getId()
            );

            $this->getEntityManager()->flush($activityOrganization);
            $this->redirect()->toRoute('contract/show', ['id'=>$activityId]);
        } catch( \Exception $e ){
            die($e->getMessage());
        }
    }
}
