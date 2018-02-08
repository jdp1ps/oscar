<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16/10/15 11:02
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\Person;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Form\WorkPackageForm;
use Oscar\Hydrator\WorkPackageHydrator;
use Oscar\Provider\Privileges;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;


/**
 * Controlleur pour les Activités de recherche.
 *
 * @package Oscar\Controller
 */
class WorkPackageController extends AbstractOscarController
{
    public function indexAction()
    {
        return [
            'entities' => $this->getEntityManager()->getRepository(WorkPackage::class)->findAll()
        ];
    }
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    /**
     *
     */
    public function restAction(){

        try {
            $this->getOscarUserContext()->checkToken();
        } catch( \Exception $e ){
            return $this->getResponseBadRequest($e->getMessage());
        }

        $idactivity = $this->params()->fromRoute('idactivity', null);
        $method = $this->getHttpXMethod();

        if( !$idactivity ){
            return $this->getResponseBadRequest("Erreur d'activité");
        }

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idactivity);

        if( !$activity ){
            return $this->getResponseBadRequest("Cette activité l'existe pas / plus");
        }

        $persons = [];
        $workpackages = [];
        /** @var WorkPackagePerson $workpackageperson */
        $workpackageperson = null;

        // Mise à jour d'un déclarant
        if( $method == 'POST' ) {
            if( !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_WORKPACKAGE_MANAGE, $activity) ){
                return $this->getResponseBadRequest("'Vous n'avez pas le droit de faire ça");
            }

            $datas = $this->getRequest()->getPost();

            if( array_key_exists('workpackageid', $datas) ){
                // Enregistrement du lot de travail
                /** @var WorkPackage $workpackage */
                $workpackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($datas['workpackageid']);
                if( !$workpackage ){
                    return $this->getResponseBadRequest('Lot de travail introuvable.');
                }
                try {
                    $workpackage->setLabel($datas['label'])
                        ->setDescription($datas['description'])
                        ->setCode($datas['code']);
                    $this->getEntityManager()->flush();
                    return $this->getResponseOk();
                } catch( \Exception $e ){
                    return $this->getResponseInternalError('Impossible de modifier le lot de travail.');
                }
            }
            else if (array_key_exists('workpackagepersonid', $datas)) {
                // Enregistrement de la durée
                $workpackageperson = $this->getEntityManager()->getRepository(WorkPackagePerson::class)->find($datas['workpackagepersonid']);
                if( !$workpackageperson ){
                    return $this->getResponseBadRequest('Déclarant introuvable.');
                }
                try {
                    $workpackageperson->setDuration($datas['duration']);
                    $this->getEntityManager()->flush();
                    return $this->getResponseOk();
                } catch( \Exception $e ){
                    return $this->getResponseInternalError('Impossible de supprimer le déclarant.');
                }
            }
            else {
                return $this->getResponseBadRequest('Données incorrecte');
            }

            return $this->getResponseBadRequest('Données incorrecte');
        }

        ///////////////////////////////////: AJOUT d'un déclarant
        if( $method == 'PUT' ){
            $data = $this->getRequest()->getPost();


            if( $data['workpackageid'] == -1) {
                try {
                    $workpackage = new WorkPackage();
                    $this->getEntityManager()->persist($workpackage);
                    $workpackage->setLabel($data['label'])
                        ->setDescription($data['description'])
                        ->setActivity($activity)
                        ->setCode($data['code']);
                    $this->getEntityManager()->flush();
                    return $this->getResponseOk();
                } catch( \Exception $e ){
                    return $this->getResponseInternalError('Impossible de créer le lot de travail.');
                }
            }

            try {
                $workpackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($data['idworkpackage']);
                if( !$workpackage ){
                    return $this->getResponseBadRequest("Le lot de travail est introuvable.");
                }
            } catch( \Exception $e ){
                return $this->getResponseBadRequest("Le lot de travail est introuvable.");
            }

            try {
                $person = $this->getEntityManager()->getRepository(Person::class)->find($data['idperson']);
                if( !$person ){
                    return $this->getResponseBadRequest("Cette personne est introuvable.");
                }
            } catch( \Exception $e ){
                return $this->getResponseBadRequest("Cette personne est introuvable.");
            }

            $declarant = $this->getEntityManager()->getRepository(WorkPackagePerson::class)->findBy([
               'person' => $person,
                'workPackage' => $workpackage
            ]);

            if( $declarant ){
                return $this->getResponseBadRequest("Cette personne est déjà déclarante de ce lot de travail.");
            }

            try {
                $declarant = new WorkPackagePerson();
                $this->getEntityManager()->persist($declarant);
                $declarant->setDuration(0);
                $declarant->setPerson($person);
                $declarant->setWorkPackage($workpackage);
                $this->getEntityManager()->flush($declarant);
            } catch ( \Exception $e ){
                return $this->getResponseBadRequest("L'ajout a bouzé : " . print_r($data, true));
            }
            return $this->getResponseOk();
        }

        if( $method == 'DELETE' ){

            if( !$this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_WORKPACKAGE_MANAGE, $activity) ){
                return $this->getResponseBadRequest("'Vous n'avez pas le droit de faire ça");
            }

            $workpackagepersonid = $this->params()->fromQuery('workpackagepersonid');
            $workpackageid = $this->params()->fromQuery('workpackageid');

            if( $workpackagepersonid ){
                $workpackageperson = $this->getEntityManager()->getRepository(WorkPackagePerson::class)->find($workpackagepersonid);
                if( !$workpackageperson ){
                    return $this->getResponseBadRequest('Déclarant introuvable.');
                }
                try {
                    $this->getEntityManager()->remove($workpackageperson);
                    $this->getEntityManager()->flush();
                    return $this->getResponseOk();
                } catch( \Exception $e ){
                    return $this->getResponseInternalError('Impossible de supprimer le déclarant.');
                }
            }
            else if( $workpackageid ){
                $workpackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($workpackageid);
                if( !$workpackage ){
                    return $this->getResponseBadRequest('Lot de travail introuvable.');
                }
                try {
                    $this->getEntityManager()->remove($workpackage);
                    $this->getEntityManager()->flush();
                    return $this->getResponseOk();
                } catch( \Exception $e ){
                    return $this->getResponseInternalError('Impossible de supprimer le lot de travail. ' . $e->getMessage());
                }
            }

            return $this->getResponseBadRequest('Données incorrecte');
        }

        ////////////////////////////////////////////// Aggrègation des personnes/rôles
        $persons = [];
        /** @var Person $person */
        foreach( $activity->getPersonsDeep() as $person ){
            $personId = $person->getPerson()->getId();
            if( !isset($persons[$personId]) ){
                $persons[$personId] = $person->getPerson()->toArray();
                $persons[$personId]['roles'] = [];
            }
            $persons[$personId]['roles'][] = $person->getRole();
        }

        $workPackages = $this->getEntityManager()->getRepository(WorkPackage::class)->createQueryBuilder('w')
            ->select('w')
            ->where('w.activity = :activity')
            ->leftJoin('w.persons', 'p')
            ->leftJoin('p.person', 'pr')
            ->setParameter('activity', $activity)
            ->orderBy('w.code')
            ->getQuery()
            ->getResult();

        $declarant = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_WORKPACKAGE_COMMIT, $activity) && $activity->hasDeclarant($this->getCurrentPerson());
        $validateur = $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity)
                || $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM, $activity);
        /** @var WorkPackage $workPackage */
        foreach( $workPackages as $workPackage){
            $wp = $workPackage->toArray();
            $wp['isDeclarant'] = $declarant;
            $wp['isValidateur'] = $validateur;
            $workpackages[] = $wp;
        }


        // Accès générale
        $response = [
            'editable' => $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_WORKPACKAGE_MANAGE),
            'isDeclarant' => $declarant,
            'isValidateur' => $validateur,
            'workpackages' => $workpackages,
            'persons' => array_values($persons)
        ];

        $json = new JsonModel();
        if( $method == 'GET' ){
            $json->setVariables($response);
            return $json;
        }

        return $this->getResponseNotImplemented('Aïe, ' . $method);
    }

    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////


    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');

        /** @var WorkPackage $entity */
        $entity = $this->getEntityManager()->getRepository(WorkPackage::class)->find($id);

        $this->getEntityManager()->remove($entity);

        $entity->getActivity()->touch();
        $this->getEntityManager()->flush();
        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé jour le lot de travail '%s:%s' dans l'activité %s", $entity->getCode(), $entity->getLabel(), $entity->getActivity()->log()),
            'Activity', $entity->getActivity()->getId());
        $this->redirect()->toRoute('contract/show',
            ['id' => $entity->getActivity()->getId()]);

    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id');

        /** @var WorkPackage $entity */
        $entity = $this->getEntityManager()->getRepository(WorkPackage::class)->find($id);

        if( !$entity ){
            return $this->getResponseNotFound(sprintf("Impossible de charger le lot '%s'", $id));
        }

        $form = new WorkPackageForm();
        $form->setHydrator(new WorkPackageHydrator());
        $form->bind($entity);


        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entity->getActivity()->touch();
                $this->getEntityManager()->flush();
                $this->getActivityLogService()->addUserInfo(
                    sprintf("a modifié le lot de travail '%s:%s' dans l'activité %s", $entity->getCode(), $entity->getLabel(), $entity->getActivity()->log()),
                    'Activity', $entity->getActivity()->getId());
                $this->redirect()->toRoute('contract/show',
                    ['id' => $entity->getActivity()->getId()]);
            }

        }

        $view = new ViewModel([
            'form' => $form,
            'entity' => $entity,
        ]);

        $view->setTemplate('oscar/work-package/form.phtml');

        return $view;

    }

    public function newAction()
    {
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($this->params()->fromRoute('idactivity'));

        $entity = new WorkPackage();
        $entity->setActivity($activity);

        $form = new WorkPackageForm();
        $form->setHydrator(new WorkPackageHydrator());
        $form->bind($entity);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if ($entity->getId()) {
                    $entity->setDateUpdated(new \DateTime());
                }
                $this->getEntityManager()->persist($entity);
                $entity->getActivity()->touch();
                $this->getEntityManager()->flush();

                $this->getActivityLogService()->addUserInfo(
                    sprintf("a ajouté le lot de travail '%s:%s' dans l'activité %s", $entity->getCode(), $entity->getLabel(), $entity->getActivity()->log()),
                    'Activity', $entity->getActivity()->getId());

                $this->redirect()->toRoute('contract/show',
                    ['id' => $activity->getId()]);
            }

        }

        $view = new ViewModel([
            'form' => $form,
            'entity' => $entity,
        ]);

        $view->setTemplate('oscar/work-package/form.phtml');

        return $view;
    }
}
